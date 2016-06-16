<?php
class Log extends QActiveRecord {
    const RECHARGE_ORDER_ID = 0;
    
    const TYPE_CN_FLIGHT = 1;
    const TYPE_IN_FLIGHT = 2;
    const TYPE_TRAIN = 3;
    const TYPE_BUS = 4;
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return '{{log}}';
    }

    public function rules() {
        return array(
            array('orderID, type, info', 'required'),
            array('orderID, type, ctime', 'numerical', 'integerOnly' => True),
            array('info', 'length', 'max' => 4096),
            array('id, orderID, type, info, ctime', 'safe', 'on' => 'search'),
        );
    }
    
    public static function add($type, $orderID, $info = '') {
        $log = new Log();
        if (!is_string($info)) {
            $info = F::unicodeDecode(json_encode($info));
        }
    
        $log->attributes = array(
            'orderID' => $orderID,
            'type' => $type,
            'info' => $info
        );
    
        if (!$log->save()) {
            Q::logModel($log);
            return F::errReturn(Rc::RC_MODEL_CREATE_ERROR);
        }
    
        return F::corReturn();
    }
}