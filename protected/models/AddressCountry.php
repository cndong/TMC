<?php
class AddressCountry extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{addressCountry}}';
    }

    public function rules() {
        return array(
            array('ctime, utime', 'required'),
            array('ctime, utime', 'numerical', 'integerOnly' => True),
            array('name', 'length', 'max' => 50),
            array('id, name, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}