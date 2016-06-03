<?php
class UserPassenger extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{userPassenger}}';
    }

    public function rules() {
        return array(
            array('userID, name, type', 'required'),
            array('userID, type, cardType, sex, deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('name', 'length', 'max' => 50),
            array('cardNo', 'length', 'max' => 25),
            array('birthday', 'length', 'max' => 10),
            array('id, userID, name, type, cardType, cardNo, birthday, sex, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'userID')
        );
    }
    
    public static function getPassengerKey($passenger) {
        $rtn = array();
        foreach (array('name', 'cardNo', 'type') as $k) {
            $rtn[] = is_object($passenger) ? $passenger->$k : $passenger[$k];
        }
        
        return implode('_', $rtn);
    }
    
    public static function getCreateOrModifyFormats($isCreate) {
        $rtn = array(
            'name' => ParamsFormat::TEXTNZ,
            'type' => ParamsFormat::PASSENGER_TYPE,
            'cardType' => ParamsFormat::CARD_TYPE,
            'cardNo' => ParamsFormat::CARD_NO,
            'birthday' => ParamsFormat::DATE,
            'sex' => ParamsFormat::SEX
        );
        if ($isCreate) {
            $rtn['userID'] = ParamsFormat::INTNZ;
        }
        
        return $rtn;
    }
    
    public static function createPassenger($params) {
        if (!($params = F::checkParams($params, self::getCreateOrModifyFormats(True)))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        if (!User::model()->findByPk($params['userID'])) {
            return F::errReturn(RC::RC_USER_NOT_EXISTS);
        }
        
        if (self::model()->findByAttributes(F::arrayGetByKeys($params, array('userID', 'cardNo', 'name', 'type')), 'deleted=:deleted', array(':deleted' => self::DELETED_F))) {
            return F::errReturn(RC::RC_PASSENGER_HAD_EXISTS);
        }
        
        $passenger = new UserPassenger();
        $passenger->attributes = $params;
        if (!$passenger->save()) {
            return F::errReturn(RC::RC_PASSENGER_CREATE_ERROR);
        }
        
        return F::corReturn($passenger);
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
            return F::errReturn(RC::RC_PASSENGER_MODIFY_ERROR);
        }
    
        return F::corReturn();
    }
}