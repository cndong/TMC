<?php
class Flight {
    public static $cityFilters = array(
        'PVG', 'NAY'
    );
    
    public static function getCNAirportDataFile() {
        return Q::getDataDocFile('flightCNAirports.txt');
    }
}