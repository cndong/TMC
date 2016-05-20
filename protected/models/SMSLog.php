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
            array('mobile, type, content, succeed, rc', 'required'),
            array('type, succeed, rc, ctime, utime', 'numerical', 'integerOnly' => True),
            array('mobile', 'length', 'max' => 11),
            array('content', 'length', 'max' => 1024),
            array('id, mobile, type, content, succeed, rc, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}