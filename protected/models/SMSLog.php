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
    
    public static function getByLimitUnit($mobile, $type, $succeed) {
        $criteria = new CDbCriteria();
        $criteria->order = 'id DESC';
        $criteria->compare('mobile', $mobile);
        $criteria->compare('type', $type);
        $criteria->compare('succeed', $succeed);
        $criteria->addBetweenCondition('ctime', Q_TIME - SMSTemplate::getLimitUnitTime($type), Q_TIME);
        
        return self::model()->findAll($criteria);
    }
}