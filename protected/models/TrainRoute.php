<?php
class TrainRoute extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{trainRoute}}';
    }

    public function rules() {
        return array(
            array('orderID, isBack, departStationCode, arriveStationCode, departTime, arriveTime, trainNo, seatType, ticketPrice', 'required'),
            array('orderID, isBack, departTime, arriveTime, seatType, ticketPrice, ctime, utime', 'numerical', 'integerOnly'=>true),
            array('departStationCode, arriveStationCode', 'length', 'max'=>50),
            array('trainNo', 'length', 'max'=>6),
            array('id, orderID, isBack, departStationCode, arriveStationCode, departTime, arriveTime, trainNo, seatType, ticketPrice, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function relations() {
        return array(
            'tickets' => array(self::HAS_MANY, 'TrainTicket', 'routeID')
        );
    }
}