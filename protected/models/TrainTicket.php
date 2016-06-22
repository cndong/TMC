<?php
class TrainTicket extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return '{{trainTicket}}';
    }

    public function rules() {
        return array(
            array('orderID, userID, departmentID, companyID, routeID, passenger, seatType, ticketPrice, ticketInfo, status', 'required'),
            array('orderID, userID, departmentID, companyID, routeID, seatType, status, ctime, utime', 'numerical', 'integerOnly' => True),
            array('passenger', 'length', 'max' => 73),
            array('ticketPrice, refundPrice', 'length', 'max' => 6),
            array('ticketInfo', 'length', 'max' => 50),
            array('ticketNo', 'length', 'max' => 10),
            array('id, orderID, userID, departmentID, companyID, routeID, passenger, seatType, ticketPrice, ticketInfo, ticketNo, refundPrice, status, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}