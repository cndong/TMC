<?php
class AddressCounty extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{addressCounty}}';
    }

    public function rules() {
        return array(
            array('name, spell, cityID', 'required'),
            array('cityID, ctime, utime', 'numerical', 'integerOnly' => True),
            array('name', 'length', 'max' => 25),
            array('spell', 'length', 'max' => 50),
            array('id, name, spell, cityID, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}