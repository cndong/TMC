<?php
class KeyManager {
    public static function getUniqueIDKey($time, $providerID) {
        return 'UniqueID' . $providerID . $time;
    }
    
    public static function getBossMenusKey($username) {
        return 'BossMenu' . $username;
    }
    
    public static function getAdminMenukKey($mobile) {
        return 'AdminMenu' . $mobile;
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
    
    public static function getTrainStationListKey() {
        return 'TrainStationList';
    }
    
    public static function getTrainListKey($departStationCode, $arriveStationCode, $departDate) {
        return 'TrainList' . $departStationCode . $arriveStationCode . $departDate;
    }
    
    public static function getTrainStopListKey($departStationCode, $arriveStationCode, $trainNo) {
        return 'TrainStopList' . $departStationCode . $arriveStationCode . $trainNo;
    }
    
    public static function getHotelCitiesKey() {
        return 'HotelCities';
    }
}