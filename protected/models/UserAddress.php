<?php
class UserAddress extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{userAddress}}';
    }

    public function rules() {
        return array(
            array('userID, name, mobile, privinceID, cityID, countyID, address', 'required'),
            array('userID, privinceID, cityID, countyID, deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('address', 'length', 'max' => 500),
            array('name' => 'length', 'max' => 50),
            array('mobile' => 'length', 'max' => 11),
            array('id, name, mobile, userID, privinceID, cityID, countyID, address, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'userID')
        );
    }
    
    public function getDescription() {
        return implode(' ', array($this->province, $this->city, $this->county, $this->address));
    }
    
    public static function getCreateOrModifyFormats($isCreate) {
        $rtn = array(
            'name' => ParamsFormat::TEXTNZ,
            'mobile' => ParamsFormat::MOBILE,
            'provinceID' => ParamsFormat::INTNZ,
            'cityID' => ParamsFormat::INTNZ,
            'countyID' => ParamsFormat::INTNZ,
            'address' => ParamsFormat::TEXTNZ
        );
        if ($isCreate) {
            $rtn['userID'] = ParamsFormat::INTNZ;
        }
        
        return $rtn;
    }
    
    private static function _getCreateOrModifyParams($params, $isCreate) {
        $formats = self::getCreateOrModifyFormats($isCreate);
        if (!($params = F::checkParams($params, $formats))) {
            return F::errReturn(RC::RC_ADDRESS_PCC_NOT_EXISTS);
        }
        if ($params['userID'] && !User::model()->findByPk($params['userID'])) {
            return F::errReturn(RC::RC_USER_NOT_EXISTS);
        }
        
        $province = AddressProvince::model()->findByPk($params['provinceID']);
        $city = AddressCity::model()->findByPk($params['cityID']);
        $county = AddressCounty::model()->findByPk($params['countyID']);
        if (!$province || !$city || !$county) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $params['province'] = $province['name'];
        $params['city'] = $province['name'];
        $params['county'] = $province['name'];
        
        return F::corReturn($params);
    }
    
    public static function createAddress($params) {
        if (!F::isCorrect($res = self::_getCreateOrModifyParams($params, True))) {
            return $res;
        }
        
        $address = new UserAddress();
        $address->attributes = $res['data'];
        if (!$address->save()) {
            return F::errReturn(RC::RC_ADDRESS_CREATE_ERROR);
        }
        
        return F::corReturn($address);
    }
    
    public static function modify($params) {
        if (!F::isCorrect($res = self::_getCreateOrModifyParams($params, False))) {
            return $res;
        }
        
        if ($res['data'] == F::arrayGetByKeys($this, array_keys($res['data']))) {
            return F::corReturn();
        }
        
        $this->attributes = $res['data'];
        if (!$this->save()) {
            return F::errReturn(RC::RC_ADDRESS_MODIFY_ERROR);
        }
        
        return F::corReturn();
    }
}