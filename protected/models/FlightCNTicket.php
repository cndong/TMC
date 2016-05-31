<?php
class FlightCNTicket extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{flightCNTicket}}';
    }

    public function rules() {
        return array(
            array('userID, departmentID, companyID, orderID, segmentID, passengerID, bigPNR, smallPNR, ticketNo, ticketPrice, airportTax, oilTax, realTicketPrice, realAirportTax, realOilTax, insurePrice, cabin, cabinClass, cabinClassName, departTime, arriveTime, status', 'required'),
            array('userID, departmentID, companyID, orderID, segmentID, passengerID, ticketPrice, airportTax, oilTax, realTicketPrice, realAirportTax, realOilTax, insurePrice, payPrice, tradeNo, cabinClass, departTime, arriveTime, status, ctime, utime', 'numerical', 'integerOnly' => True),
            array('bigPNR, smallPNR', 'length', 'max' => 6),
            array('ticketNo', 'length', 'max' => 14),
            array('cabin', 'length', 'max' => 2), 
            array('cabinClassName', 'length', 'max' => 50),
            array('id, userID, departmentID, companyID, orderID, segmentID, passengerID, bigPNR, smallPNR, ticketNo, ticketPrice, airportTax, oilTax, realTicketPrice, realAirportTax, realOilTax, insurePrice, payPrice, tradeNo, cabin, cabinClass, cabinClassName, departTime, arriveTime, status, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}