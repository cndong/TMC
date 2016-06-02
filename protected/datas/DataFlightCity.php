<?php
class DataFlightCity {
    public static function getCNCities() {
        $cacheKey = KeyManager::getFlightCNCitiesKey();
        if (!($cities = Yii::app()->cache->get($cacheKey))) {
            $cities = json_decode(file_get_contents(Flight::getCNCitiesDataFile()), True);
            Yii::app()->cache->set($cacheKey, $cities);
        }

        return $cities;
    }
    
    public static function getINCities() {
        return array();
    }
}