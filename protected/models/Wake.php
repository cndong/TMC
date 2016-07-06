<?php
class Wake {
    const REQUEST_URL = 'che.zaiwen.com:8888/';
    const BOOK_SUCC = 1;
    const BOOK_FAIL = 2;
    
    public static function request($params, $method, $isPost = False) {
        $curl = new Curl();
        
        if ($isPost) {
            $curl->setPostDataType(Curl::POST_DATA_TYPE_BUILD);
            Q::log($params. 'wake.post');
        }
        
        if (!Curl::isCorrect($res= $curl->postJ(self::REQUEST_URL . $method, $params))) {
            Q::log($res);
            return F::errReturn(F::getCurlError($res));
        }
        $isPost && Q::log($res. 'wake.response');
        
        if (!empty($res['data']['code']) && $res['data']['code'] != F::STATUS_SUCCESS) {
            return F::errReturn(RC::RC_B_STATUS_WAKE_ERROR);
        }
        
        return F::corReturn($res['data']);
    }
    
    public static function getBusListKey($departCity, $arriveCity, $departDate, $busNo, $departTime) {
        return implode('_', func_get_args());
    }
    
    public static function getBusList($departCity, $arriveCity, $departDate) {
        $cacheKey = 'busGetList_' . implode('_', func_get_args());
        if ($data = Yii::app()->cache->get($cacheKey)) {
            return F::corReturn($data);
        }
    
        if (!F::isCorrect($res = self::request(array('departCity' => $departCity, 'arriveCity' => $arriveCity, 'date' => $departDate, 'sign' => md5($departDate)), 'app_get_station'))) {
            return $res;
        }
        if (empty($res['data']) || !is_array($res['data'])) {
            return F::corReturn(array());
        }
          
        $data = array();
        $k2k = array(
            'fromCityName' => 'departCity', 'fromStationName' => 'departStation',
            'toCityName' => 'arriveCity', 'toStationName' => 'arriveStation',
            'fromTime' => 'departTime', 'busNumber' => 'busNo', 'fullPrice' => 'price',
            'busType' => 'seatType', 'bookable' => 'isCanBook'
        );
    
        $data = array();
        foreach ($res['data'] as $line) {
            $tmp = F::arrayChangeKeys($line, $k2k);
            $tmp['departDate'] = $departDate;
            $tmp['servicePrice'] = 5;
            $tmp['ticketNum'] = $tmp['isCanBook'] ? 5 : 0;
            
            $key = self::getBusListKey($departCity, $arriveCity, $departDate, $tmp['busNo'], $tmp['departTime']);
            $data[$key] = $tmp;
        }
    
        Yii::app()->cache->set($cacheKey, $data, 60 * 5);
    
        return F::corReturn($data);
    }
    
    public static function _getBusList($departCity, $arriveCity, $departDate) {
        $cacheKey = 'busGetList_' . implode('_', func_get_args());
        if ($data = Yii::app()->cache->get($cacheKey)) {
            return F::corReturn($data);
        }
        
        //过后删除
        $res = F::corReturn(BusList::getList($departCity, $arriveCity, $departDate));
        
        /*过后恢复
        if (!F::isCorrect($res = self::request(array('departCity' => $departCity, 'arriveCity' => $arriveCity, 'date' => $departDate, 'sign' => md5($departDate)), 'app_get_station'))) {
            return $res;
        }
        if (empty($res['data']) || !is_array($res['data'])) {
            return F::corReturn(array());
        }
     
        $data = array();
        $k2k = array(
            'fromCityName' => 'departCity', 'fromStationName' => 'departStation',
            'toCityName' => 'arriveCity', 'toStationName' => 'arriveStation',
            'fromTime' => 'departTime', 'busNumber' => 'busNo', 'fullPrice' => 'price',
            'busType' => 'seatType', 'bookable' => 'isCanBook'
        );
        */
        
        $data = array();
        foreach ($res['data'] as $line) {
            //过后删除
            $keys = array('departCity', 'arriveCity', 'departStation', 'arriveStation', 'departDate', 'departTime', 'busNo', 'price', 'seatType');
            $tmp = F::arrayGetByKeys($line, $keys);
            $tmp['isCanBook'] = 1;
            
            /*过后恢复
            $tmp = Q::arrayChangeKeys($line, $k2k);
            $tmp['departDate'] = $departDate;
            */
            
            $tmp['servicePrice'] = 5;
            $key = self::getBusListKey($departCity, $arriveCity, $departDate, $tmp['busNo'], $tmp['departTime']);
            $data[$key] = $tmp;
        }

        //过后恢复
        Yii::app()->cache->set($cacheKey, $data, 60 * 5);
        
        return F::corReturn($data);
    }
    
    public static function book($params) {
        $params['mobile'] = '17071527080'; //默认统一使用此手机号，避免携程发送短信
        $k2k = array(
            'contacter' => 'contactName', 'mobile' => 'contactPhone', 'certificateNo' => 'contactId',
            'ctime' => 'create_timestamp', 'departCity' => 'from', 'arriveCity' => 'to', 'departStation' => 'fromStation',
            'arriveStation' => 'toStation', 'orderPrice' => 'amount', 'passengerNum' => 'ticketNum'
        );
        
        if (!is_array($params['passengers'])) {
            $res = BusOrder::parsePassengerStr($params['passengers']);
            $params['passengers'] = $res['data'];
        }
        
        $dataPassengers = array();
        foreach ($params['passengers'] as $passenger) {
            $dataPassengers[] = array(
                'passengerName' => $passenger['passengerName'],
                'passengerPhone' => '17071527080',
                'passengerId' => $passenger['passengerCertificateNo'],
                'passengerType' => 1,
                'seatType' => $params['seatType']
            );
        }
        
        $data = F::arrayChangeKeys($params, $k2k);
        $data['date'] = date('Y-m-d', $params['departTimestamp']);
        $data['startTime'] = date('H:i', $params['departTimestamp']);
        $data['startDateTime'] = date('YmdHi', $params['departTimestamp']);
        $data['create_date'] = date('YmdHi', $params['ctime']);
        $data['create_timestamp'] = $params['ctime'];
        $data['ticketPrice'] = $params['ticketPrice'] * $params['passengerNum'];
        $data['ticketServicePrice'] = 0;
        $data['passengers'] = $dataPassengers;
        $data['purchaseProxy'] = 'ss_bus_app';
        
        $sign = md5($data['contactPhone']);
        
        $data = array('orderNo' => $params['id'], 'data' => $data);
        if (!F::isCorrect($res = self::request(array('order' => json_encode($data), 'sign' => $sign), 'app_order', True))) {
            return $res;
        }

        if (empty($res['data']['data']['order_id'])) {
            return F::errReturn(F::STATUS_WAKE_RETURN_DATA_ERROR);
        }
        
        return F::corReturn(array('wakeOrderID' => $res['data']['data']['order_id']));
    }
    
    public static function getBookResult($orderIDs) {
        $orderIDs = implode('-', $orderIDs);
        if (!F::isCorrect($res = self::request(array('orderNoList' => $orderIDs, 'sign' => md5($orderIDs)), 'app_get_ticket_info'))) {
            return $res;
        }
        
        return F::corReturn($res['data']['data']);
    } 
}