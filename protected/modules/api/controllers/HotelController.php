<?php
class HotelController extends ApiController {
    public function actionCityList() {
        $rtn = array('cityList' => array(), 'hotList' => array());
        $oldCityList = $cityList = DataHotelCity::getCities();
        $cityList = F::array_sort_inKey($cityList, 'citySpell', SORT_ASC, SORT_REGULAR);
        foreach ($cityList as &$city) {
            unset($city['CountryId']);
            unset($city['CountryName']);
            unset($city['ProvinceId']);
            unset($city['ProvinceName']);
            $city['firstChar'] = $firstChar = strtoupper($city['citySpell']{0});
            if (!isset($rtn['cityList'][$firstChar])) {
                $rtn['cityList'][$firstChar] = array(
                    'cities' => array(),
                    'firstChar' => $firstChar
                );
            }
            
            $rtn['cityList'][$firstChar]['cities'][] = $city;
        }
        
        $rtn['cityList'] = array_values($rtn['cityList']);
        $rtn['hotList'] = array_values(F::arrayGetByKeys($oldCityList, array('0101', '0201', '2003')));
        $this->corAjax($rtn);
    }
    
    public function actionHotelList() {
        if (!($params = F::checkParams($_GET,
                array(
                        'cityId' => '!' . ParamsFormat::HOTEL_CITY_ID . '--0',
                        'star' => '!' . ParamsFormat::INTNZ . '--0',
                        'lon' => '!' . ParamsFormat::FLOATNZ . '--0',
                        'lat' => '!' . ParamsFormat::FLOATNZ . '--0',
                        'pageSize' => '!' . ParamsFormat::INTNZ . '--10',
                        'page' => '!' . ParamsFormat::INTNZ . '--1',
                        'checkIn' => ParamsFormat::DATE,
                        'checkOut' => ParamsFormat::DATE,
                ))))
            $this->errAjax(RC::RC_VAR_ERROR);
        
        $rtn = array();
        $criteria = new CDbCriteria();
        foreach (array('cityId', 'star') as $type) {
            if (!empty($params[$type])) {
                if($type == 'star') $criteria->addInCondition($type, Hotel::$starArray[$params[$type]]);
                else $criteria->compare('t.' . $type, $params[$type]);
            }
        }
        if($params['lon'] && $params['lat'])
            $criteria->order = ' ACOS(SIN(('.$params['lat'].' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$params['lat'].' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$params['lon'].' * 3.1415) / 180 - (lon * 3.1415) / 180 ) ) * 6380 asc';
        //分页
        $count = Hotel::model()->count($criteria);
        $criteria->limit = $params['pageSize'];
        $criteria->offset = ($params['page']-1)*$criteria->limit;
        
        $hotels = Hotel::model()->findAll($criteria);
        $rtn = $this->_getHotels($hotels, $params);
        //数量不够再查一次
        if(count($rtn)<9){
            $criteria->offset = ($params['page']-1+1)*$criteria->limit;
            $hotels = Hotel::model()->findAll($criteria);
            $rtn = array_merge($rtn, $this->_getHotels($hotels, $params));
        }
        
        $this->corAjax(array('hotelList'=>$rtn));
    }
    
    private function _getHotels($hotels, $params){
        $allLowPrice = Hotel::getAllLowPrice($hotels, $params);
        $rtn = array();
        foreach ($hotels as $hotel) {
            $hotelArray = $hotel->getAttributes(array('hotelId', 'hotelName', 'address', 'star', 'image', 'lowPrice'));
            $hotelArray['image'] = $hotel->getMainImage();
            foreach (Hotel::$starArray as  $key => $stars){
                foreach ($stars as $star){
                    if($hotelArray['star'] == $star) {
                        $hotelArray['star'] = $key; break 2;
                    }
                }
            }
            $hotelArray['lowPrice'] = (string) (isset($allLowPrice[$hotel->hotelId]) ? $allLowPrice[$hotel->hotelId] : 0);
            $hotelArray['lowPrice'] && $rtn[] = $hotelArray;
        }
        return $rtn;
    }
    
    public function actionHotelDetail() {
        if (!($params = F::checkParams($_GET,
                array(
                        'hotelId' => ParamsFormat::INTNZ,
                        'checkIn' => ParamsFormat::DATE,
                        'checkOut' => ParamsFormat::DATE,
                ))))
            $this->errAjax(RC::RC_VAR_ERROR);
        
        $hotelAR = Hotel::model()->findByPK($params['hotelId']);
        if(!$hotelAR) $this->errAjax(RC::RC_H_HOTEL_NOT_EXISTS);
        else{
             $hotel = $hotelAR->attributes;
             
             //图片
             $mainImage = array('ImageId'=>rand(90000, 999999), 'ImageName'=>'主图', 'ImageUrl' =>$hotelAR->getMainImage());
             $hotel['images'] = array($mainImage);
             $hotelImages = HotelImage::model()->findAll("hotelId={$params['hotelId']}");
             foreach ($hotelImages as $hotelImage) {
                 $hotel['images'][] = F::arrayGetByKeys($hotelImage, array('ImageId', 'ImageName', 'ImageUrl'));
             }
            
             //地标
             $hotel['landmarks'] = array();
             $hotelLandmarks = HotelLandmark::model()->findAll("hotelId={$params['hotelId']}");
             foreach ($hotelLandmarks as $hotelLandmark) {
                 $hotel['landmarks'][] = F::arrayGetByKeys($hotelLandmark, array('Landid', 'LandName'));
             }
             
            $hotel['rooms'] = $priceArray = array();
            $city = DataHotelCity::getCity($hotel['cityId']);
            if(F::isCorrect($res= ProviderCNBOOKING::request('RatePlanSearch',
                    array(
                            'CountryId' => $city['CountryId'],
                            'ProvinceId' => $city['ProvinceId'],
                            'CityId' => $city['cityCode'],
                            'HotelId' => $params['hotelId'],
                            'CheckIn' => $params['checkIn'],
                            'CheckOut' => $params['checkOut']
                    ))) && $res['data']){
                if(is_array($res['data']['Hotels']) && is_array($res['data']['Hotels']['Hotel']['Rooms']['Room'])){
                    $rooms  =  $res['data']['Hotels']['Hotel']['Rooms']['Room'];
                    if(isset($res['data']['Hotels']['Hotel']['Rooms']['RoomCount']) && $res['data']['Hotels']['Hotel']['Rooms']['RoomCount'] ==1)  $rooms = array($rooms);
                    
                    //去除[]  breakfastType description
                    foreach ($rooms as &$room){
                        if(isset($room['RatePlans']) && $room['RatePlans']['RatePlanCount']){
                            if($room['RatePlans']['RatePlanCount'] == 1) $room['RatePlans']['RatePlan'] = array($room['RatePlans']['RatePlan']);
                            foreach ($room['RatePlans']['RatePlan'] as &$ratePlan){
                                unset($ratePlan['Description']);
                                unset($ratePlan['BreakfastType']);
                            }
                        }
                    }
                    
                    //Rate PriceAndStatu json单层就转化为对象! 多层就转化成数组 我要数组!!!
                    foreach ($rooms as &$room){
                        if(isset($room['Rates']) && $room['Rates']['RateCount']){
                            if($room['Rates']['RateCount']==1) $room['Rates']['Rate'] = array($room['Rates']['Rate']);
                            foreach ($room['Rates']['Rate'] as &$rate) {
                                if($rate['PriceAndStatus']['PriceAndStatuCount']==1) $rate['PriceAndStatus']['PriceAndStatu'] = array($rate['PriceAndStatus']['PriceAndStatu']);
                                foreach ($rate['PriceAndStatus']['PriceAndStatu'] as &$priceAndStatu) {
                                    $priceAndStatu['LastCancelTime'] = $priceAndStatu['LastCancelTime'] && strtotime($priceAndStatu['LastCancelTime']) > time() ? $priceAndStatu['LastCancelTime'] : '';
                                    if(!Q::isProductEnv()) $priceAndStatu['Count'] = (string)rand(0, 10);
                                    if(!Q::isProductEnv()) if(rand(0, 10)>5) $priceAndStatu['LastCancelTime'] = date('Y/n/d G:i:s', time()+3600);
                                    $priceArray[] = $priceAndStatu['Price'];
                                }
                            }
                        }
                    }
                    
                    $hotel['rooms'] = F::changeArrKey($rooms);
                }
                sort($priceArray);
                Hotel::setLowPrice($params['hotelId'], array_merge($params, array('cityId'=>$city['cityCode'])), $priceArray, false);
            }
            $this->corAjax(array('hotelDetail'=>$hotel));
        }
    }
    
    public function actionPreBookingCheck() {
        if(F::isCorrect($res= ProviderCNBOOKING::request('PreBookingCheck',
                array(
                        'HotelId' => $_POST['hotelId'],
                        'RoomId' => $_POST['roomId'],
                        'RateplanId' => $_POST['rateplanId'],
                        'CheckIn' => $_POST['checkIn'],
                        'CheckOut' => $_POST['checkOut'],
                        'RoomCount' => 1,
                        'OrderAmount' => $_POST['orderAmount'],
                ))) && $res['data']){
            if(is_array($res['data']) && $res['data']['ReturnCode'] == ProviderCNBOOKING::PREBOOKINGCHECK_SUCCESS){
                $this->corAjax();
            }else $this->errAjax(RC::RC_H_HOTEL_PREBOOKINGCHECK_ERROR);
        }
    }
    
    public function actionBooking() {
        $this->onAjax(HotelOrder::createOrder($_POST));
    }
    
    public function actionOrderList() {
        if (!($params = F::checkParams($_GET,
                array(
                        'userID' => ParamsFormat::INTNZ,
                        'pageSize' => '!' . ParamsFormat::INTNZ . '--10',
                        'page' => '!' . ParamsFormat::INTNZ . '--1',
                ))))
            $this->errAjax(RC::RC_VAR_ERROR);
        
        $criteria = new CDbCriteria();
        $criteria->order = 'id desc';
        $criteria->compare('userID', $_GET['userID']);
        
        //分页
        $count = HotelOrder::model()->count($criteria);
        $criteria->limit = $params['pageSize'];
        $criteria->offset = ($params['page']-1)*$criteria->limit;
        
        $rtn = array();
        $orders = HotelOrder::model()->findAll($criteria);
        foreach ($orders as $order) {
            $tmp = F::arrayGetByKeys($order, array('id', 'hotelName', 'roomName', 'checkIn', 'checkOut', 'roomCount', 'orderPrice', 'ctime'));
            $tmp['status'] = HotelStatus::getUserDes($order['status']);
            $rtn[] = $tmp;
        }
    
        $this->corAjax(array('orderList' => $rtn));
    }
    
    public function actionReviewOrderList() {
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!($user = User::model()->findByPk($_GET['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
    
        $rtn = array();
        if ($user->isReviewer) {
            $criteria = new CDbCriteria();
            $criteria->order = 'id desc';
            $criteria->compare('departmentID', $user->departmentID);
            $criteria->compare('status', HotelStatus::WAIT_CHECK);
            $orders = HotelOrder::model()->findAll($criteria);
            foreach ($orders as $order) {
                $tmp = F::arrayGetByKeys($order, array('id', 'hotelName', 'roomName', 'checkIn', 'checkOut', 'roomCount', 'orderPrice', 'ctime'));
                $tmp['status'] = HotelStatus::getUserDes($order['status']);
                $rtn[] = $tmp;
            }
        }
    
        $this->corAjax(array('reviewOrderList' => $rtn));
    }
    
    public function actionReview() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'orderId' => ParamsFormat::INTNZ, 'status' => ParamsFormat::BOOL)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
    
        if (!($order = HotelOrder::model()->findByPk($params['orderId']))) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
    
        $status = $params['status'] ? HotelStatus::CHECK_SUCC : HotelStatus::CHECK_FAIL;
    
        $this->onAjax($order->changeStatus($status, array('reviewerID' => $user)));
    }
    
    private function _getFlags($status, $order) {
        return array(
                'status' => HotelStatus::getUserDes($status),
                'isReview' => $status == HotelStatus::WAIT_CHECK,
                'isCancel' => in_array($status, array(HotelStatus::WAIT_CHECK, HotelStatus::WAIT_PAY)),
                'isPay' => $status == HotelStatus::WAIT_PAY,
                'isRefund' => in_array($status, array(HotelStatus::BOOK_SUCC)) && $order->lastCancelTime != ''
        );
    }
    
    public function actionOrderDetail() {
        if (!($params = F::checkParams($_GET,
                array(
                    'orderId' => ParamsFormat::INTNZ,
                ))))
            $this->errAjax(RC::RC_VAR_ERROR);
        
        $order = HotelOrder::model()->findByPk($params['orderId']);
        if (!$order)$this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        $order->lastCancelTime == '0000-00-00 00:00:00' && $order->lastCancelTime = '';
        $hotel = Hotel::model()->findByPk($order->hotelId);
        $this->corAjax(array('orderDetail' => array_merge(
                                                                        $order->attributes,
                                                                        array(
                                                                                'address' => $hotel->address,
                                                                                'lon' =>  $hotel->lon,
                                                                                'lat' =>  $hotel->lat,
                                                                                'star'=>  $hotel->star,
                                                                       ),
                                                                       $this->_getFlags($order->status, $order))
                ));
    }
    
    public function actionCancel() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'orderId' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
    
        if (!($order = HotelOrder::model()->findByPk($params['orderId'])) || $order->userID != $user->id) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
    
        $this->onAjax($order->changeStatus(HotelStatus::CANCELED));
    }
    
    public function actionRefund() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'orderId' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        if (!($order = HotelOrder::model()->findByPk($params['orderId'])) || $order->userID != $user->id) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }else $_POST['oID'] = $order->oID;
        $this->onAjax($order->changeStatus(HotelStatus::APPLY_RFD, $_POST));
    }
    
}