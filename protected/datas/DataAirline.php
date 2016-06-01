<?php
class DataAirline {
    public static function getAirlines() {
        $cacheKey = KeyManager::getFlightAirlinesKey();
        if (!($airlines = Yii::app()->cache->get($cacheKey))) {
            $airlines = json_decode(file_get_contents(Flight::getAirlineDataFile()), True);
            Yii::app()->cache->set($cacheKey, $airlines);
        }
    
        return $airlines;
    }
}