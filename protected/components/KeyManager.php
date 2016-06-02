<?php
class KeyManager {
    public static function getUniqueIDKey($time, $providerID) {
        return 'UniqueID' . $providerID . $time;
    }
    
    public static function getBossMenusKey($username) {
        return 'BossMenu' . $username;
    }
    
    public static function getFlightCNAirportsKey() {
        return 'FlightAirports';
    }
    
    public static function getFlightCNCitiesKey() {
        return 'FlightCities';
    }
    
    public static function getFlightAirlinesKey() {
        return 'FlightAirlines';
    }
    
    public static function getFlightCNFlightListKey($departCityCode, $arriveCityCode, $departDate, $returnData = '') {
        return 'FlightCNFlightList' . $departCityCode . $arriveCityCode . $departDate . $returnData;
    }
    
    public static function getFlightCNFlightDetailKey($departCityCode, $arriveCityCode, $departDate, $flightNo) {
        return 'FlightCNFlightList' . $departCityCode . $arriveCityCode . $departDate . $flightNo;
    }
}