<?php
class UserContacter extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{userContacter}}';
    }

    public function rules() {
        return array(
            array('userID, name, mobile', 'required'),
            array('userID, deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('name', 'length', 'max' => 50),
            array('mobile', 'length', 'max' => 11),
            array('id, userID, name, mobile, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'userID')
        );
    }
    
    public static function getCreateOrModifyFormats($isCreate) {
        $rtn = array('name' => ParamsFormat::TEXTNZ, 'mobile' => ParamsFormat::MOBILE);
        if ($isCreate) {
            $rtn['userID'] = ParamsFormat::INTNZ;
        }
        
        return $rtn;
    }
    
    public static function createContacter($params) {
        if (!($params = F::checkParams($params, self::getCreateOrModifyFormats(True)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($params['userID']))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        if (self::model()->findByAttributes(array('mobile' => $params['mobile']))) {
            $this->errAjax(RC::RC_CONTACTER_HAD_EXISTS);
        }
        
        $contacter = new UserContacter();
        $contacter->attributes = $params;
        if (!$contacter->save()) {
            return F::errReturn(RC::RC_CONTACTER_CREATE_ERROR);
        }
        
        return F::corReturn($contacter);
    }
    
    public function modify($params) {
        if (!($params = F::checkParams($params, self::getCreateOrModifyFormats(False)))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        if ($params == F::arrayGetByKeys($this, array_keys($params))) {
            return F::corReturn();
        }
        
        $this->attributes = $params;
        if (!$this->save()) {
            return F::errReturn(RC::RC_CONTACTER_MODIFY_ERROR);
        }
        
        return F::corReturn();
    }
}