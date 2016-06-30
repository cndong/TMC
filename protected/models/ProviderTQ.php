<?php
class ProviderTQ extends ProviderT {
    public function __construct($id = '') {
        $this->_curl = Curl::getInstance(__CLASS__ . $id);
    }
    
    private static $_urls = array(
        'stationList' => array('url' => '/Train/StationList'),
        'trainList' => array('url' => '/Train/TrainList'),
        'stopList' => array('url' => '/Train/PassStationList'),
        'book' => array('url' => 'Train/CreateOrder', 'method' => 'POST'),
    );
    
    private function _request($type, $data = array()) {
        $urlConfig = self::$_urls[$type];
        $isPost = isset($urlConfig['method']) && $urlConfig['method'] == 'POST';
        $method = $isPost ? 'postJ' : 'getJ';
        
        $merchantConfig = QEnv::$providers[Dict::BUSINESS_TRAIN][ProviderT::PROVIDER_Q];
        $url = $merchantConfig['url'] . self::$_urls[$type]['url'];
        $data['requestTime'] = Q_TIME;
        $data['merchantID'] = $merchantConfig['merchantID'];
        $data['sign'] = F::getSignature($data, $merchantConfig['key']);
        if (!$isPost) {
            $url .= '?' . F::buildQuery($data);
            $data = array();
        }
        
        if (!Curl::isCorrect($res = $this->_curl->$method($url, $data))) {
            return F::errReturn(F::getCurlError($res));
        }
        
        if (!F::isCorrect($res['data'])) {
            return $res['data'];
        }
        
        return F::corReturn($res['data']['data']);
    }
    
    public function pGetStationList($isReload = False) {
        $cacheKey = KeyManager::getTrainStationListKey();
        if ($isReload || !($data = Yii::app()->cache->get($cacheKey))) {
            $data = array();
            $res = $this->_request('stationList');
            foreach ($res['data']['list'] as $station) {
                $data[$station['code']] = $station;
            }
            
            Yii::app()->cache->set($cacheKey, $data);
        }
        
        return $data;
    }
    
    public function pGetTrainList($params, $isReload = False) {
        $cacheKey = KeyManager::getTrainListKey($params['departStationCode'], $params['arriveStationCode'], $params['departDate']);
        if ($isReload || !($data = Yii::app()->cache->get($cacheKey))) {
            $data = array();
            if (F::isCorrect($res = $this->_request('trainList', $params))) {
                $trainList = $res['data']['trainList'];
                foreach ($trainList as $trainInfo) {
                    $trainInfo['duration'] *= 60;
                    $trainInfo['departTime'] = strtotime($trainInfo['departDate'] . ' ' . $trainInfo['departTime']);
                    $trainInfo['arriveTime'] = $trainInfo['departTime'] + $trainInfo['duration'];
                    
                    $seats = array();
                    foreach ($trainInfo['seats'] as $seat) {
                        $seat['seatPrice'] *= 100;
                        $seats[$seat['seatType']] = $seat;
                    }
                    
                    $trainInfo['seats'] = $seats;
                    $data[$trainInfo['trainNo']] = $trainInfo;
                }
                
                Yii::app()->cache->set($cacheKey, $data, 60 * 5);
            }
        }
        
        return $data;
    }
    
    public function pGetStopList($params, $isReload = False) {
        $cacheKey = KeyManager::getTrainStopListKey($params['departStationCode'], $params['arriveStationCode'], $params['trainNo']);
        if ($isReload || !($data = Yii::app()->cache->get($cacheKey))) {
            $data = array();
            if (F::isCorrect($res = $this->_request('stopList', $params))) {
                $data = $res['data']['passStationList'];
                
                Yii::app()->cache->set($cacheKey, $data);
            }
        }
        
        return $data;
    }
    
    public function pBook($order) {
        $routes = $order->getRoutes();
        $route = $routes['departRoute'];
        $params = F::arrayGetByKeys($order, array('contactName', 'contactMobile'));
        $params['merchantOrderID'] = $order->id;
        $params['queryKey'] = md5($order->id);
        $params['journeyType'] = $order->isRound ? DictTrain::JOURNEY_TYPE_WF : DictTrain::JOURNEY_TYPE_DC;
        $passengers = array();
        foreach (UserPassenger::parsePassengers($order->passengers) as $passenger) {
            $tmp = F::arrayGetByKeys($passenger, array('type', 'name', 'cardType', 'cardNo'));
            $tmp['birth'] = $passenger['birthday'];
            $tmp['seatType'] = $route->seatType;
            $tmp['ticketPrice'] = $route->ticketPrice;
        }
        $params['insureID'] = $order->isInsured ? DictTrain::INSURE_ID : 0;
        $params['insurePrice'] = $order->insurePrice;
        $params['isMergeNotice'] = Dict::STATUS_FALSE;
        $params['isAcceptNoSeat'] = Dict::STATUS_FALSE;
        
        $params['passengers'] = json_encode($passengers);
        $params = array_merge($params, F::arrayGetByKeys($route, array('trainNo', 'departStationCode', 'arriveStationCode')));
        $params['departDate'] = date('Y-m-d', $route->departTime);
        $params['departTime'] = date('H:i', $route->departTime);
        
        return $this->_request('book', $params);
    }
}