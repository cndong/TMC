<?php
class SMSLog extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{smsLog}}';
    }

    public function rules() {
        return array(
            array('mobile, type, sign, content, succeed', 'required'),
            array('type, sign, succeed, ctime, utime', 'numerical', 'integerOnly' => True),
            array('mobile', 'length', 'max' => 11),
            array('content', 'length', 'max' => 1024),
            array('id, mobile, type, sign, content, succeed, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}