<?php
class DataAirport {
    public static function getCNCities() {
        $cacheKey = KeyManager::getFlightCNCitiesKey();
        if (!($data = Yii::app()->cache->get($cacheKey))) {
            $airports = self::getCNAiports();
            
            $data = array();
            foreach ($airports as $airport) {
                if (in_array($airport['cityCode'], Flight::$cityFilters)) {
                    continue;
                }
                
                $data[$airport['cityCode']] = array(
                    'cityCode' => $airport['cityCode'],
                    'cityName' => $airport['cityName'],
                    'citySpell' => empty($airport['citySpell']) ? $airport['airportSpell'] : $airport['citySpell'],
                    'cityShortSpell' => empty($airport['cityShortSpell']) ? $airport['airportShortSpell'] : $airport['cityShortSpell'],
                );
            }
            
            Yii::app()->cache->set($cacheKey, $data);
        }
        
        return $data;
    }
    
    public static function getCNAiports() {
        $cacheKey = KeyManager::getFlightCNAirportsKey();
        if (!($data = Yii::app()->cache->get($cacheKey))) {
            $airports = json_decode(file_get_contents(Flight::getCNAirportDataFile()), True);
            
            $data = array();
            foreach ($airports as $airport) {
                $data[$airport['airportCode']] = $airport;
            }
            
            Yii::app()->cache->set($cacheKey, $data);
        }
        
        return $data;
    }
    
    public static function getINCities() {
        return array();
    }
    
    public static function getINAirports() {
        return array();
    }
}