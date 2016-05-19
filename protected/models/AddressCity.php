<?php
class AddressCity extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{addressCity}}';
    }

    public function rules() {
        return array(
            array('name, provinceID, countryID, isDomestic, ctime, utime', 'required'),
            array('provinceID, countryID, isDomestic, ctime, utime', 'numerical', 'integerOnly' => True),
            array('code', 'length', 'max' => 3),
            array('name', 'length', 'max' => 25),
            array('spell', 'length', 'max' => 100),
            array('id, code, name, spell, provinceID, countryID, isDomestic, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}