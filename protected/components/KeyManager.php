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
}