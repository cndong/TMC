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
            array('userID, name, flightType, trainType, busType', 'required'),
            array('userID, flightType, trainType, busType, cardType, sex, deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('name', 'length', 'max' => 50),
            array('cardNo', 'length', 'max' => 25),
            array('birthday', 'length', 'max' => 10),
            array('id, userID, name, flightType, trainType, busType, cardType, cardNo, birthday, sex, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'userID')
        );
    }
    
    public function getTypeByBusiness($businessID) {
        $key = Dict::$businesses[$businessID]['str'] . 'Type';
        
        return $this->$key;
    }
    
    public static function concatPassenger($passenger, $businessID = 0) {
        $fields = array('name', 'cardType', 'cardNo', 'birthday', 'sex', 'id');
        $attributes = F::arrayGetByKeys($passenger, $fields);
        if (isset($passenger['type'])) {
            $attributes['type'] = $passenger['type'];
        } elseif (!empty($businessID)) {
            $attributes['type'] = $passenger[Dict::$businesses[$businessID]['str'] . 'Type'];
        }
        
        return implode(',', $attributes);
    }
    
    public static function concatPassengers($passengers) {
        $rtn = array();
        foreach ($passengers as $passenger) {
            $rtn[] = self::concatPassenger($passenger);
        }
    
        return implode('|', $rtn);
    }
    
    public static function parsePassenger($passenger) {
        $fields = array('name' , 'type', 'cardType', 'cardNo', 'birthday', 'sex', 'id');
        $passenger = explode(',', $passenger);
    
        return array_combine($fields, $passenger);
    }
    
    public static function parsePassengers($passengers) {
        $rtn = array();
    
        $passengers = explode('|', $passengers);
        foreach ($passengers as $passenger) {
            $passenger = self::parsePassenger($passenger);
            $rtn[$passenger['id']] = $passenger;
        }
    
        return $rtn;
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
            'sex' => ParamsFormat::SEX,
            'businessID' => '!' . ParamsFormat::BUSINESS_ID . '--' . Dict::BUSINESS_FLIGHT
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
        
        foreach (Dict::$businesses as $businessID => $businessConfig) {
            $params[$businessConfig['str'] . 'Type'] = $businessID == $params['businessID'] ? $params['type'] : Dict::PASSENGER_TYPE_ADULT;
        }
        $businessID = $params['businessID'];
        unset($params['businessID'], $params['type']);
        
        if (!User::model()->findByPk($params['userID'])) {
            return F::errReturn(RC::RC_USER_NOT_EXISTS);
        }
        
        if (self::model()->findByAttributes(F::arrayGetByKeys($params, array('userID', 'cardNo', 'name', Dict::$businesses[$businessID]['str'] . 'Type')), 'deleted=:deleted', array(':deleted' => self::DELETED_F))) {
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
    
        $params[Dict::$businesses[$params['businessID']]['str'] . 'Type'] = $params['type'];
        $businessID = $params['businessID'];
        unset($params['businessID'], $params['type']);
        
        if (self::model()->findByAttributes(F::arrayGetByKeys($params, array('userID', 'cardNo', 'name', Dict::$businesses[$businessID]['str'] . 'Type')), 'deleted=:deleted', array(':deleted' => self::DELETED_F))) {
            return F::errReturn(RC::RC_PASSENGER_HAD_EXISTS);
        }
        
        $this->attributes = $params;
        if (!$this->save()) {
            return F::errReturn(RC::RC_PASSENGER_MODIFY_ERROR);
        }
    
        return F::corReturn();
    }
}