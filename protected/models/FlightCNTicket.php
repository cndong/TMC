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
            array('userID, departmentID, companyID, orderID, segmentID, passenger, isInsured, flightNo, craftCode, craftType, ticketPrice, airportTax, oilTax, realTicketPrice, realAirportTax, realOilTax, insurePrice, cabin, cabinClass, cabinClassName, departTime, arriveTime, departTerm, arriveTerm, status', 'required'),
            array('userID, departmentID, companyID, orderID, segmentID, ticketID, ticketPrice, airportTax, oilTax, realTicketPrice, realAirportTax, realOilTax, insurePrice, resignHandlePrice, refundHandlePrice, refundPrice, payPrice, tradeNo, craftType, cabinClass, departTime, arriveTime, status, deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('flightNo', 'length', 'max' => 6),
            array('bigPNR, smallPNR', 'length', 'max' => 6),
            array('ticketNo', 'length', 'max' => 14),
            array('craftCode', 'length', 'max' => 15),
            array('cabinClassName', 'length', 'max' => 50),
            array('passenger', 'length', 'max' => 73),
            array('departTerm, arriveTerm', 'length', 'max' => 2),
            array('id, userID, departmentID, companyID, orderID, segmentID, ticketID, passenger, isInsured, bigPNR, smallPNR, ticketNo, ticketPrice, airportTax, oilTax, realTicketPrice, realAirportTax, realOilTax, insurePrice, resignHandlePrice, refundHandlePrice, refundPrice, payPrice, tradeNo, flightNo, craftCode, craftType, cabin, cabinClass, cabinClassName, departTime, arriveTime, departTerm, arriveTerm, status, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}