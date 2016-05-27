<?php
require_once implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'extensions', 'flightTB', 'TopSdk.php'));

//为保数据一致性和可维护性，只能被ProviderF调用
class ProviderFTB extends ProviderF {
    
    public static $cabinClasses = array(
        0 => array('name' => '头等舱', 'code' => DictFlight::CABIN_TD),
        1 => array('name' => '商务舱', 'code' => DictFlight::CABIN_SW),
        2 => array('name' => '经济舱', 'code' => DictFlight::CABIN_JJ)
    );
    
    private static $_client = Null;
    
    public function request($req) {
        if (!self::$_client) {
            self::$_client = new TopClient();
            self::$_client->appkey = QEnv::$providers[Dict::BUSINESS_FLIGHT][ProviderF::PROVIDER_TB]['appkey'];
            self::$_client->secretKey = QEnv::$providers[Dict::BUSINESS_FLIGHT][ProviderF::PROVIDER_TB]['secretKey'];
            self::$_client->format = 'json';
        }
        $data = json_decode(json_encode(self::$_client->execute($req)), True);
        
        Q::log($req, 'tb');
        Q::log($data, 'tb');
        
        if (isset($data['code'])) {
            return F::errReturn(RC::RC_P_ERROR);
        }
        
        return F::corReturn($data['result']);
    }
    
    private function _initFlights($flights) {
        $rtn = array();
        foreach ($flights as $flightKey => $flight) {
            $tmp = F::arrayChangeKeys($flight, array(
                'depAirportCode' => 'departAirportCode',
                'arrAirportCode' => 'arriveAirportCode',
                'depTerm' => 'departTerm',
                'arrTerm' => 'arriveTerm',
                'airlineCode' => 'airlineCode',
                'flightType' => 'craftCode',
                'flight' => 'flightNo',
                'fees' => 'adultAirportTax',
                'meals' => 'isHaveMeal',
                'stops' => 'stopNum',
                'cabins' => array()
            ));
            $tmp['departTime'] = $flight['depTime'] / 1000;
            $tmp['arriveTime'] = $flight['arrTime'] / 1000;
            $tmp['duration'] = $tmp['arriveTime'] - $tmp['departTime'];
            $tmp['childAirportTax'] = $tmp['babyAirportTax'] = 0;
            $tmp['adultOilTax'] = $tmp['childOilTax'] = $tmp['babyOilTax'] = 0;
            
            $rtn[$flightKey] = $tmp;
        }
        
        return $rtn;
    }
    
    private function _initCabins($cabins) {
        $cabinsRtn = array();
        $cabinClassesRtn = array();
        foreach ($cabins as $cabinKey => $cabin) {
            $cabinClass = self::$cabinClasses[$cabin['cabinClass']]['code'];
            $cabinsRtn[$cabinKey] = array(
                'airlineCode' => $cabin['airline'],
                'cabin' => $cabin['cabin'],
                'cabinClass' => $cabinClass,
            );
            $cabinClassesRtn[$cabinClass] = DictFlight::$cabinClasses[$cabinClass]['name'];
        }
        
        return array($cabinsRtn, $cabinClassesRtn);
    }
    
    private function _initFlightList($data) {
        $rtn = array();
        
        $rtn['airlineMap'] = json_decode($data['airline_info_map'], True);
        $rtn['airportMap'] = json_decode($data['airport_info_map'], True);
        $rtn['cityMap'] = json_decode($data['city_info_map'], True);
        $rtn['craftMap'] = json_decode($data['flight_type_info_map'], True);
        list($_, $rtn['cabinClassMap']) = $this->_initCabins(json_decode($data['cabin_info_map'], True));
        
        $flights = $this->_initFlights(json_decode($data['flight_info_map'], True));
        $rtn['flights'] = &$flights;
        $rtn['routes'] = array();
        
        $flightRoutes = $data['items']['at_n_search_item_v_o'];//航程列表，一个航程有一个或多个航段
        foreach ($flightRoutes as $routeIndex => $flightRoute) {
            if (count($flightRoute['segments']['at_n_search_segment_v_o']) > 1) {
                //暂停多航段功能，恢复时把这段去掉即可
                continue;
            }
            
            if (!$flightRoute['is_qijian']) {
                continue;
            }
            $route = array(
                'segments' => array()
            );
            
            //航段列表
            $routeKey = array();
            foreach ($flightRoute['segments']['at_n_search_segment_v_o'] as $flightSegment) {
                $flightKey = self::getFlightKey($flightSegment['dep_city'], $flightSegment['arr_city'], $flightSegment['flight_no']);
                $routeKey[] = $flightKey;
                $flights[$flightKey]['departCityCode'] = $flightSegment['dep_city'];
                $flights[$flightKey]['arriveCityCode'] = $flightSegment['arr_city'];
                $flights[$flightKey]['cabins'][$flightSegment['cabin']] = array(
                    'cabin' => $flightSegment['cabin'],
                    'cabinClass' => self::$cabinClasses[$flightSegment['cabin_class']]['code'],
                    'cabinNum' => $flightSegment['cabin_num'],
                    'discount' => floatval(sprintf('%.1f', $flightSegment['price'] * 10 / $flightSegment['basic_cabin_price'])),
                    'adultPrice' => $flightSegment['price'],
                    'childPrice' => $flightSegment['basic_cabin_price'] * DictFlight::RATE_CHILD,
                    'babyPrice' => $flightSegment['basic_cabin_price'] * DictFlight::RATE_BABY,
                    'standardPrice' => $flightSegment['basic_cabin_price'],
                    'rule' => self::getRule($flights[$flightKey]['airlineCode'], $flightSegment['cabin'], date('Y-m-d', $flights[$flightKey]['departTime']))
                );
                
                $route['segments'][] = array(
                    'flightKey' => $flightKey
                );
            }
            
            $route['routeKey'] = self::getRouteKey($routeKey);
            
            $rtn['routes'][$route['routeKey']] = $route;
        }
        
        foreach ($flights as $index => $flight) {
            if (empty($flight['cabins'])) {
                unset($flights[$index]);
            }
        }
        
        return F::corReturn($rtn);
    }
    
    public function pGetCNFlightList($params, $searchType = 'outbound') {
        $req = new TripJipiaoNsearchOwSearchRequest();
        $req->setDepCityCode($params['departCityCode']);
        $req->setArrCityCode($params['arriveCityCode']);
        $req->setDepDate($params['departDate']);
        $req->setSearchType($searchType);
        if (isset($params['flightNo'])) {
            $req->setFlightNo($params['flightNo']);
        }
        
        if (!F::isCorrect($res = $this->request($req))) {
            return $res;
        }
        
        if (!F::isCorrect($res = $this->_initFlightList($res['data']))) {
            return $res;
        }
        
        return F::corReturn($res['data']);
    }
    
    public function pGetCNFlightDetail($params) {
        if (F::isCorrect($res = $this->pGetCNFlightList($params))) {
            return $res;
        }
        $outboundData = $res['data'];
        
        if (!F::isCorrect($res = $this->pGetCNFlightList($params, 'lowprice'))) {
            return $res;
        }
        $lowpriceData = $res['data'];
        
        if (!F::isCorrect($res = $this->pGetCNFlightList($params, 'gaoduan'))) {
            return $res;
        }
        
        return F::corReturn(F::mergeArrayInt($outboundData, $lowpriceData, $res['data']));
    }
}