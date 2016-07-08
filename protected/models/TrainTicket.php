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
            array('orderID, userID, departmentID, companyID, routeID, passenger, providerPassengerID, trainNo, departStationCode, arriveStationCode, departTime, arriveTime, seatType, ticketPrice, insurePrice, ticketInfo, status', 'required'),
            array('orderID, userID, departmentID, companyID, routeID, departTime, arriveTime, seatType, ticketPrice, insurePrice, refundPrice, status, ctime, utime', 'numerical', 'integerOnly' => True),
            array('passenger', 'length', 'max' => 73),
            array('trainNo', 'length', 'max' => 6),
            array('departStationCode, arriveStationCode, ticketInfo', 'length', 'max' => 50),
            array('ticketNo', 'length', 'max' => 10),
            array('providerPassengerID', 'length', 'max' => 15),
            array('id, orderID, userID, departmentID, companyID, routeID, passenger, providerPassengerID, trainNo, departStationCode, arriveStationCode, departTime, arriveTime, seatType, ticketPrice, insurePrice, ticketInfo, ticketNo, refundPrice, status, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}