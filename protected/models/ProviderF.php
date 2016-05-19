<?php
//提供十分便民到供应商的转换
class ProviderF extends Provider {
    const PROVIDER_TB = 2;
    
    public static $providersConfig = array(
        self::PROVIDER_TB => array('name' => '淘宝', 'str' => 'TB')
    );
    
    public static function getCNCityList() {
        return DataAirport::getCNCities();
    }
    
    public static function getINCityList() {
        return DataAirport::getINCities();
    }
    
    public static function getCNAirportList() {
        return DataAirport::getCNAiports();
    }
    
    public static function getINAirportList() {
        return DataAirport::getINAirports();
    }
    
    public static function encryptOrderParams($orderParams) {
        return F::encryptWithBase64(json_encode($orderParams), QEnv::$orderParamsKey[Dict::BUSINESS_FLIGHT]);
    }
    
    public static function decryptOrderParams($orderParams) {
        return json_decode(F::decryptWithBase64($orderParams, QEnv::$orderParamsKey[Dict::BUSINESS_FLIGHT]), True);
    }
    
    public static function getRule($airportCode, $cabin, $date) {
        /*
        array(
        'CZ'=>array(
            'F'=>array(
    			'2016-05-10'=>'text',
    			'2016-06-19'=>'text',
    			'2016-08-10'=>'text',
    			)
    		)
        )
        */
        return '这是退改规则';
    }
    
    public static function getRouteKey($flightKeys) {
        return md5(implode('-', $flightKeys));
    }
    
    public static function getFlightKey($departCityCode, $arriveCityCode, $flightNo) {
        return $departCityCode . $arriveCityCode . $flightNo;
    }
    
    //提交时使用routeKey, {segmentKey:segmentParams}
    public static function addPriceSortParams($data) {
        foreach ($data['flights'] as &$flight) {
            $flight['orderParams'] = self::encryptOrderParams($flight);
        }
        
        foreach ($data['routes'] as &$route) {
            $route['adultPrice'] = $route['childPrice'] = $route['babyPrice'] = $route['standardPrice'] = 0;
            foreach ($route['segments'] as &$segment) {
                uasort($data['flights'][$segment['flightKey']]['cabins'], function($a, $b) {return $a['price'] > $b['price']; });
                $cabin = current($data['flights'][$segment['flightKey']]['cabins']);
                $segment['adultPrice'] = $cabin['price'];
                $segment['childPrice'] = $cabin['standardPrice'] * DictFlight::RATE_CHILD;
                $segment['babyPrice'] = $cabin['standardPrice'] * DictFlight::RATE_BABY;
                $segment['standardPrice'] = $cabin['standardPrice'];
                
                $route['adultPrice'] += $segment['adultPrice'];
                $route['childPrice'] += $segment['childPrice'];
                $route['babyPrice'] += $segment['babyPrice'];
                $route['standardPrice'] += $segment['standardPrice'];
            }
        }
        
        return $data;
    }
    
    public static function removeKey($data) {
        $data['routes'] = array_values($data['routes']);
        foreach ($data['flights'] as &$flight) {
            $flight['cabins'] = array_values($flight['cabins']);
        }
        
        return $data;
    }
    
    public static function getCNFlightList($params, $isWithKey = False, $isReload = False) {
        $formats = array(
            'departCityCode' => ParamsFormat::F_CN_CITY_CODE,
            'arriveCityCode' => ParamsFormat::F_CN_CITY_CODE,
            'departDate' => ParamsFormat::DATE,
            'returnDate' => '!' . ParamsFormat::DATE . '--'
        );
        if (!F::checkParams($params, $formats)) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        if (!F::isCorrect($res = ProviderF::getProvider(self::PROVIDER_TB)->pGetCNFlightList($params))) {
            return $res;
        }
        
        $data = self::addPriceSortParams($res['data']);
        $data = $isWithKey ? $data : self::removeKey($data);
        
        return F::corReturn($data);
    }
    
    public static function getCNFlightDetail($params, $isWithKey = False, $isReload = False) {
        $formats = array(
            'departCityCode' => ParamsFormat::F_CN_CITY_CODE,
            'arriveCityCode' => ParamsFormat::F_CN_CITY_CODE,
            'departDate' => ParamsFormat::DATE,
            'flightNo' => ParamsFormat::F_FLIGHT_NO,
            'returnDate' => '!' . ParamsFormat::DATE . '--'
        );
        if (!F::checkParams($params, $formats)) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        if (!F::isCorrect($res = ProviderF::getProvider(self::PROVIDER_TB)->pGetCNFlightDetail($params, $isWithKey, $isReload))) {
            return $res;
        }
        
        $data = self::addPriceSortParams($res['data']);
        $data = $isWithKey ? $data : self::removeKey($data);
        
        return F::corReturn($data);
    }
    
    public static function getINFlightList($params, $isWithKey = False, $isReload = False) {
        return ProviderF::getProvider(self::PROVIDER_TB)->pGetINFlightList($params, $isWithKey, $isReload);
    }
}