<?php
class AddressProvince extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{AddressProvince}}';
    }

    public function rules() {
        return array(
            array('name, spell, countryID, ctime, utime', 'required'),
            array('countryID, ctime, utime', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>50),
            array('spell', 'length', 'max'=>100),
            array('id, name, spell, countryID, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}