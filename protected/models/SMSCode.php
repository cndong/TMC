<?php
class SMSCode extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{smsCode}}';
    }

    public function rules() {
        return array(
            array('mobile, code, status', 'required'),
            array('status, ctime, utime', 'numerical', 'integerOnly' => true),
            array('mobile', 'length', 'max' => 11),
            array('code', 'length', 'max' => 6),
            array('id, mobile, code, status, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}