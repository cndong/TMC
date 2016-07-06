<?php
class DataBusCity {
    public static function getAllCities() {
        $cacheKey = 'busCityes';
        if (!($rtn = Yii::app()->cache->get($cacheKey))) {
            $rtn = self::parseCityFile('all');
            //Yii::app()->cache->set($cacheKey, $rtn);
        }
        
        return $rtn;
    }
    
    public static function getFromCities() {
        $cacheKey = 'busFromCities';
        if (!($rtn = Yii::app()->cache->get($cacheKey))) {
            $rtn = self::parseCityFile('');
            Yii::app()->cache->set($cacheKey, $rtn);
        }
        
        return $rtn ? $rtn : array();
    }
    
    public static function getToCities($cityName) {
        $cacheKey = 'busToCities-' . $cityName;
        if (!($rtn = Yii::app()->cache->get($cacheKey))) {
            $rtn = self::parseCityFile($cityName);
            Yii::app()->cache->set($cacheKey, $rtn);
        }
        return $rtn ? $rtn : array();
    }
    
    public static function parseCityFile($fromCityName = '') {
        $rtn = array();
        $isAll = $fromCityName == 'all';
        $content = trim(file_get_contents(Q::getDataDocFile('busCity.txt')));
        $cities = explode('@', $content);
        foreach ($cities as $city) {
            list($fromCity, $toCities) = explode(',', $city);
            $fromCity = explode('|', $fromCity);
            $fromCity = array(
                'name' => $fromCity[1],
                'spell' => $fromCity[2],
                'shortSpell' => $fromCity[3],
                'char' => ucfirst(substr($fromCity[2], 0, 1))
            );
            
            if (!empty($fromCityName)) {
                if (!$isAll && $fromCityName != $fromCity['name']) {
                    continue;
                }
                $to = array();
                $toCities = explode('-', $toCities);
                foreach ($toCities as $toCity) {
                    $toCity = explode('|', $toCity);
                    if (count($toCity) < 3) {
                        continue;
                    }
                    $to[$toCity[0]] = array(
                        'name' => $toCity[0],
                        'spell' => $toCity[1],
                        'shortSpell' => $toCity[2],
                        'char' => ucfirst(substr($toCity[1], 0, 1))
                    );
                }
                
                if ($fromCityName == $fromCity['name']) {
                    return $to;
                } elseif ($isAll) {
                    $fromCity['to'] = $to;
                    $rtn[$fromCity['name']] = $fromCity;
                }
            } else {
                $rtn[$fromCity['name']] = $fromCity;
            }
        }
        
        return empty($fromCityName) || $isAll ? $rtn : array();
    }
}