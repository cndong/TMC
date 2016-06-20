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
    
    public function pGetTrainList($departStationCode, $arriveStationCode, $departDate, $isReload = False) {
        $cacheKey = KeyManager::getTrainListKey($departStationCode, $arriveStationCode, $departDate);
        if ($isReload || !($data = Yii::app()->cache->get($cacheKey))) {
            $data = array();
            if (F::isCorrect($res = $this->_request('trainList', array('departStationCode' => $departStationCode, 'arriveStationCode' => $arriveStationCode, 'departDate' => $departDate)))) {
                $data = $res['data']['trainList'];
                foreach ($data as &$trainInfo) {
                    $trainInfo['duration'] *= 60;
                    $trainInfo['departTime'] = strtotime($trainInfo['departDate'] . ' ' . $trainInfo['departTime']);
                    $trainInfo['arriveTime'] = $trainInfo['departTime'] + $trainInfo['duration'];
                }
                
                Yii::app()->cache->set($cacheKey, $data, 60 * 5);
            }
        }
        
        return $data;
    }
    
    public function pGetStopList($departStationCode, $arriveStationCode, $trainNo, $isReload = False) {
        $cacheKey = KeyManager::getTrainStopListKey($departStationCode, $arriveStationCode, $trainNo);
        if ($isReload || !($data = Yii::app()->cache->get($cacheKey))) {
            $data = array();
            if (F::isCorrect($res = $this->_request('stopList', array('departStationCode' => $departStationCode, 'arriveStationCode' => $arriveStationCode, 'trainNo' => $trainNo)))) {
                $data = $res['data']['passStationList'];
                
                Yii::app()->cache->set($cacheKey, $data);
            }
        }
        
        return $data;
    }
    
    public function pCreateOrder($order) {
        
    }
}