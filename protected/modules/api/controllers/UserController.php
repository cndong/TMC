<?php
class UserController extends ApiController {
    private function _getUserInfo($user) {
        $rtn = F::arrayGetByKeys($user, array('id', 'mobile', 'name', 'ctime'));
        $rtn['company'] = $user->company->name;
        $rtn['department'] = $user->department->name;
        
        return $rtn;
    }
    
    public function actionLogin() {
        if (!F::checkParams($_POST, array('mobile' => ParamsFormat::MOBILE, 'password' => ParamsFormat::TEXTNZ)) || !($password = $_POST['password'])) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $identity = new UserIdentity($_POST['mobile'], $password);
        if (!$identity->authenticate()) {
            $this->errAjax(RC::RC_LOGIN_FAILED);
        }
        
        $this->corAjax($this->_getUserInfo(User::model()->findByPk($identity->id)));
    }
    
    public function actionUserInfo() {
        if (!F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($_GET['userID'], array('with' => array('company', 'department')))) || $user->deleted) {
            return F::errReturn(RC::RC_USER_NOT_EXISTS);
        }
        
        $this->corAjax($this->_getUserInfo($user));
    }
    
    public function actionSendResetCode() {
        if (!F::checkParams($_POST, array('mobile' => ParamsFormat::MOBILE))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }

        if (!($user = User::model()->findByAttributes(array('mobile' => $_POST['mobile'], 'deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        if (!F::isCorrect($res = SMSCode::send($_POST, SMSCode::TYPE_FORGET_PASSWD))) {
            $this->onAjax($res);
        }
        
        $this->corAjax();
    }
    
    public function actionResetPassword() {
        if (!($params = F::checkParams($_POST, array('mobile' => ParamsFormat::MOBILE, 'code' => ParamsFormat::SMS_CODE, 'password' => ParamsFormat::TEXTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($smsCode = SMSCode::getByType($params['mobile'], SMSCode::TYPE_FORGET_PASSWD, SMSCode::STATUS_SENDED))) {
            $this->errAjax(RC::RC_SMS_CODE_NOT_EXISTS);
        }
        
        if (!F::isCorrect($res = $smsCode->verify($params['code']))) {
            $this->errAjax(RC::RC_SMS_CODE_NOT_CORRECT);
        }
        
        $user = User::model()->findByAttributes(array('mobile' => $params['mobile'], 'deleted' => User::DELETED_F));

        $this->onAjax($user->modifyPassword($params['password']));
    }
    
    public function actionModifyPassword() {
        if (!F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'password' => ParamsFormat::TEXTNZ)) || !($password = base64_decode($_POST['password']))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($_POST['userID']))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        $this->onAjax($user->modifyPassword($_POST['password']));
    }
    
    public function actionContacterList() {
        if (!F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $rtn = array();
        $contacters = UserContacter::model()->findAllByAttributes(array('userID' => $_GET['userID'], 'deleted' => UserContacter::DELETED_F));
        foreach ($contacters as $contacter) {
            $rtn[] = F::arrayGetByKeys($contacter, array('name', 'mobile'));
        }
        
        $this->corAjax(array('contacterList' => $rtn));
    }
    
    public function actionAddContacter() {
        $this->onAjax(UserContacter::createContacter($_POST));
    }
    
    public function actionModifyContacter() {
        if (!F::checkParams($_POST, array('contacterID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($contacter = UserContacter::model()->findByPk($_POST['contacterID'])) || $contacter->deleted) {
            $this->errAjax(RC::RC_CONTACTER_NOT_EXISTS);
        }
        
        $this->onAjax($contacter->modify($_POST));
    }
    
    public function actionDeleteContacter() {
        if (!F::checkParams($_POST, array('contacterID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($contacter = UserContacter::model()->findByPk($_POST['contacterID'])) || $contacter->deleted) {
            $this->errAjax(RC::RC_CONTACTER_NOT_EXISTS);
        }
        
        $contacter->deleted = UserContacter::DELETED_T;
        if (!$contacter->save()) {
            $this->errAjax(RC::RC_CONTACTER_DELETE_ERROR);
        }
        
        $this->corAjax();
    }
    
    private function _getPassengerAttributes($passenger) {
        return F::arrayGetByKeys($passenger, array('id', 'name', 'type', 'cardType', 'cardNo', 'birthday', 'sex'));
    }
    
    public function actionPassengerList() {
        if (!F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $rtn = array();
        $passengers = UserPassenger::model()->findAllByAttributes(array('userID' => $_GET['userID'], 'deleted' => UserContacter::DELETED_F));
        foreach ($passengers as $passenger) {
            $rtn[] = $this->_getPassengerAttributes($passenger);
        }
        
        $this->corAjax(array('passengerList' => $rtn));
    }
    
    public function actionAddPassenger() {
        if (!F::isCorrect($res = UserPassenger::createPassenger($_POST))) {
            $this->onAjax($res);
        }
        
        $this->corAjax($this->_getPassengerAttributes($res['data']));
    }
    
    public function actionModifyPassenger() {
        if (!F::checkParams($_POST, array('passengerID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($passenger = UserPassenger::model()->findByPk($_POST['passengerID'])) || $passenger->deleted) {
            $this->errAjax(RC::RC_PASSENGER_NOT_EXISTS);
        }
        
        if (!F::isCorrect($res = $passenger->modify($_POST))) {
            $this->onAjax($res);
        }
        
        $this->corAjax($this->_getPassengerAttributes($passenger));
    }
    
    public function actionDeletePassenger() {
        if (!F::checkParams($_POST, array('passengerID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($passenger = UserPassenger::model()->findByPk($_POST['passengerID'])) || $passenger->deleted) {
            $this->errAjax(RC::RC_PASSENGER_NOT_EXISTS);
        }
        
        $passenger->deleted = UserPassenger::DELETED_T;
        if (!$passenger->save()) {
            $this->errAjax(RC::RC_PASSENGER_DELETE_ERROR);
        }
        
        $this->corAjax();
    }
    
    private function _getAddressAttributes($address) {
        return F::arrayGetByKeys($address, array('id', 'name', 'mobile', 'provinceID', 'cityID', 'countyID', 'province', 'city', 'county', 'address'));
    }
    
    public function actionAddressList() {
        if (!F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $rtn = array();
        $addresses = UserAddress::model()->findAllByAttributes(array('userID' => $_GET['userID'], 'deleted' => UserContacter::DELETED_F));
        foreach ($addresses as $address) {
            $rtn[] = $this->_getAddressAttributes($address);
        }
        
        $this->corAjax(array('addressList' => $rtn));
    }
    
    public function actionAddAddress() {
        if (!F::isCorrect($res = UserAddress::createAddress($_POST))) {
            $this->onAjax($res);
        }
        
        $this->corAjax($this->_getAddressAttributes($res['data']));
    }
    
    public function actionModifyAddress() {
        if (!F::checkParams($_POST, array('addressID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($address = UserAddress::model()->findByPk($_POST['addressID'])) || $address->deleted) {
            $this->errAjax(RC::RC_ADDRESS_NOT_EXISTS);
        }
        
        if (!F::isCorrect($res = $address->modify($_POST))) {
            $this->onAjax($res);
        }
        
        $this->corAjax($this->_getAddressAttributes($address));
    }
    
    public function actionDeleteAddress() {
        if (!F::checkParams($_POST, array('addressID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($address = UserAddress::model()->findByPk($_POST['addressID'])) || $address->deleted) {
            $this->errAjax(RC::RC_ADDRESS_NOT_EXISTS);
        }
        
        $address->deleted = UserAddress::DELETED_T;
        if (!$address->save()) {
            $this->errAjax(RC::RC_ADDRESS_DELETE_ERROR);
        }
        
        $this->corAjax();
    }
    
    public function actionUploadAvatar() {
       if (!F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($_GET['userID']))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        $upload = $this->upload($_GET['userID']);
        if ($upload['errorCode'] != UPLOAD_ERR_OK) {
            $this->errAjax(RC::RC_USER_UPLOAD_ERROR);
        }
        $this->onAjax($user->setAvatar($upload['fileName']));
    }
    
    public function upload($userId){
        $upload = array('errorCode'=>UPLOAD_ERR_OK);
        $uploadedFile = CUploadedFile::getInstanceByName('upload');
        if($uploadedFile){
            $path = "/avatar";
            if (!is_dir(Yii::getPathOfAlias('webroot').$path)) {
                mkdir(Yii::getPathOfAlias('webroot').$path);
            }
            $oldfileName = explode(".", $uploadedFile->name);
            $fileName = md5($userId.$oldfileName).'.'.end($oldfileName);
            $fullPath = $path.'/'.$fileName;
            if($uploadedFile->saveAs(Yii::getPathOfAlias('webroot').$fullPath)){
                $upload['fileName'] = $fileName;
            }else $upload['errorCode'] = $uploadedFile->getError();
        }else $upload['errorCode']  = -1;
        if ($upload['errorCode'] != UPLOAD_ERR_OK) Q::log($upload, 'upload.error');
        return $upload;
    }
    
}