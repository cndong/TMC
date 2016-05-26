<?php
class FlightCNSegment extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{flightCNSegment}}';
    }

    public function rules() {
        return array(
            array('orderID, isBack, flightNo, airlineCode, craftCode, craftType, cabin, cabinClass, departCityCode, arriveCityCode, departAirportCode, arriveAirportCode, departTime, arriveTime, adultPrice, childPrice, babyPrice, adultAirportTax, childAirportTax, babyAirportTax, adultOilTax, childOilTax, babyOilTax', 'required'),
            array('orderID, isBack, craftType, departTime, arriveTime, ctime, utime', 'numerical', 'integerOnly' => True),
            array('adultPrice, childPrice, babyPrice, adultAirportTax, childAirportTax, babyAirportTax, adultOilTax, childOilTax, babyOilTax', 'numerical'),
            array('flightNo', 'length', 'max' => 6),
            array('airlineCode, cabin', 'length', 'max' => 2),
            array('craftCode, departCityCode, arriveCityCode, departAirportCode, arriveAirportCode', 'length', 'max' => 3),
            array('cabinClass', 'length', 'max' => 1),
            array('batchNo', 'length', 'max' => 15),
            array('id, orderID, isBack, flightNo, airlineCode, craftCode, craftType, cabin, cabinClass, departCityCode, arriveCityCode, departAirportCode, arriveAirportCode, departTime, arriveTime, adultPrice, childPrice, babyPrice, adultAirportTax, childAirportTax, babyAirportTax, adultOilTax, childOilTax, babyOilTax, batchNo, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
}