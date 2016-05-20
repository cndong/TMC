<?php
class UserController extends ApiController {
    private function _getUserInfo($user) {
        $rtn = F::arrayGetByKeys($user, array('id', 'mobile', 'name', 'ctime', 'companyID', 'departmentID'));
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
        
        $this->corAjax($this->__getUserInfo($user));
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
            $rtn = F::arrayGetByKeys($contacter, array('name', 'mobile'));
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
    
    public function actionPassengerList() {
        if (!F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $rtn = array();
        $passengers = UserPassenger::model()->findAllByAttributes(array('userID' => $_GET['userID'], 'deleted' => UserContacter::DELETED_F));
        foreach ($passengers as $passenger) {
            $rtn = F::arrayGetByKeys($passenger, array('name', 'type', 'cardType', 'cardNo', 'birthday', 'sex'));
        }
        
        $this->corAjax(array('passengerList' => $rtn));
    }
    
    public function actionAddPassenger() {
        $this->onAjax(UserPassenger::createPassenger($_POST));
    }
    
    public function actionModifyPassenger() {
        if (!F::checkParams($_POST, array('passengerID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($passenger = UserPassenger::model()->findByPk($_POST['passengerID'])) || $passenger->deleted) {
            $this->errAjax(RC::RC_PASSENGER_NOT_EXISTS);
        }
        
        $this->onAjax($passenger->modify($_POST));
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
    
    public function actionAddressList() {
        if (!F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $rtn = array();
        $addresses = UserAddress::model()->findAllByAttributes(array('userID' => $_GET['userID'], 'deleted' => UserContacter::DELETED_F));
        foreach ($addresses as $address) {
            $rtn = F::arrayGetByKeys($addresses, array('name', 'mobile', 'provinceID', 'cityID', 'countyID', 'province', 'city', 'county', 'address'));
        }
        
        $this->corAjax(array('addressList' => $rtn));
    }
    
    public function actionAddAddress() {
        $this->onAjax(UserAddress::createAddress($_POST));
    }
    
    public function actionModifyAddress() {
        if (!F::checkParams($_POST, array('addressID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($address = UserAddress::model()->findByPk($_POST['addressID'])) || $address->deleted) {
            $this->errAjax(RC::RC_ADDRESS_NOT_EXISTS);
        }
        
        $this->onAjax($address->modify($_POST));
    }
    
    public function actionDeleteAddress() {
        if (!F::checkParams($_POST, array('addressID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($address = UserAddress::model()->findByPk($_POST['passengerID'])) || $address->deleted) {
            $this->errAjax(RC::RC_ADDRESS_NOT_EXISTS);
        }
        
        $address->deleted = UserAddress::DELETED_T;
        if (!$address->save()) {
            $this->errAjax(RC::RC_ADDRESS_DELETE_ERROR);
        }
        
        $this->corAjax();
    }
}