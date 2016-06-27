<?php
class HotelController extends ApiController {
    public function actionCityList() {
        $rtn = array('cityList' => array(), 'hotList' => array());
        $cityList = DataHotelCity::getCities();
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
        $rtn['hotList'] = array_values(F::arrayGetByKeys($cityList, array('0101', '0201', '2003')));
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
        
        $criteria = new CDbCriteria();
        foreach (array('cityId', 'star') as $type) {
            if (!empty($params[$type])) {
                $criteria->compare('t.' . $type, $params[$type]);
            }
        }
        if($params['lon'] && $params['lat'])
            $criteria->order = ' ACOS(SIN(('.$params['lat'].' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$params['lat'].' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$params['lon'].' * 3.1415) / 180 - (lon * 3.1415) / 180 ) ) * 6380 asc';
        
        //分页
        $count = Hotel::model()->count($criteria);
        $criteria->limit = $params['pageSize'];
        $criteria->offset = ($params['page']-1)*$criteria->limit;
        
        $rtn = array();
        $hotels = Hotel::model()->findAll($criteria);
        foreach ($hotels as $hotel) {
            $hotel = $hotel->getAttributes(array('hotelId', 'hotelName', 'address', 'star', 'image', 'lowPrice'));
            $images = array('http://userimg.qunar.com/imgs/201501/21/66I5P26rcOOsfY2A6180.jpg', 'http://userimg.qunar.com/imgs/201501/21/66I5P26rcOOsfY2A6180.jpg');
            $hotel['image'] = $images[rand(0, 1)];
            $hotel['lowPrice'] = rand(100, 500);
            $rtn[] = $hotel;
        }
        $this->corAjax(array('hotelList'=>$rtn)); 
    }
    
    public function actionHotelDetail() {
        if (!($params = F::checkParams($_GET,
                array(
                        'hotelId' => ParamsFormat::INTNZ,
                        'checkIn' => ParamsFormat::DATE,
                        'checkOut' => ParamsFormat::DATE,
                ))))
            $this->errAjax(RC::RC_VAR_ERROR);
        
        $hotel = Hotel::model()->findByPK($params['hotelId']);
        if(!$hotel) $this->errAjax(RC::RC_H_HOTEL_NOT_EXISTS);
        else{
             $hotel = $hotel->attributes;
             
             //图片
             $imagesRand = array('http://userimg.qunar.com/imgs/201501/21/66I5P26rcOOsfY2A6180.jpg', 'http://userimg.qunar.com/imgs/201501/21/66I5P26rcOOsfY2A6180.jpg');
             $mainImage = array('ImageId'=>rand(90000, 999999), 'ImageName'=>'主图', 'ImageUrl' => $imagesRand[rand(0, 1)]);
             $hotel['images'] = json_decode($hotel['images'] ,true);
             $hotel['images'] = $hotel['images'] ? $hotel['images'] : array(); 
             array_unshift($hotel['images'], $mainImage);
             
             //地标
             $hotel['landmarks'] = json_decode($hotel['landmarks'] ,true);
             $hotel['landmarks'] = $hotel['landmarks'] ? $hotel['landmarks'] : array();
             
            $hotel['rooms'] = array();
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
                            
                            if($room['Rates']['RateCount']==1){
                                $room['Rates']['Rate'] = array($room['Rates']['Rate']);
                            }
                            foreach ($room['Rates']['Rate'] as &$rate) {
                                if($rate['PriceAndStatus']['PriceAndStatuCount']==1){
                                    $rate['PriceAndStatus']['PriceAndStatu'] = array($rate['PriceAndStatus']['PriceAndStatu']);
                                }
                                //测试count 正式要去掉
                                foreach ($rate['PriceAndStatus']['PriceAndStatu'] as &$priceAndStatu) {
                                    $priceAndStatu['Count'] = rand(0,1);
                                }
                            }
                            
                        }
                    }
                    
                    $hotel['rooms'] = F::changeArrKey($rooms);
                }
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
/*         $_POST =  array(
                    'hotelId' => 1106,
                    'hotelName'=>'北京西苑饭店',
                    'roomId' => 127663,
                    'rateplanId' => 1184,
                    'checkIn' => '2016-07-20',
                    'checkOut' => '2016-07-22',
                    'roomCount' => 2,
                    'orderPrice'=>2240,
                    'bookName' => '测试_王东',
                    'bookPhone' => '15952016956',
                    'guestName' => '测试_王本',
                    'reason'=>'去上海',
                    'lastCancelTime'=>'',
                    'merchantID'=>30,
                    'userID'=>5,
            );  */
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
            $tmp = F::arrayGetByKeys($order, array('id', 'hotelName', 'checkIn', 'checkOut', 'roomCount', 'orderPrice', 'ctime'));
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
                $tmp = F::arrayGetByKeys($order, array('id', 'hotelName', 'checkIn', 'checkOut', 'roomCount', 'orderPrice', 'ctime'));
                $tmp['status'] = HotelStatus::getUserDes($order['status']);
                $rtn[] = $tmp;
            }
        }
    
        $this->corAjax(array('reviewOrderList' => $rtn));
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
                                                                        array(
                                                                                'status' => HotelStatus::getUserDes($order['status']),
                                                                                'isCancel' => HotelStatus::getUserCando($order['status'], HotelStatus::CANCELED),
                                                                                'isRefund' => HotelStatus::getUserCando($order['status'], HotelStatus::APPLY_RFD),
        ))));
    }
    
    public function actionCancel() {
/*         $_POST['orderId'] = 200022;
        $_POST['userID'] = 5; */
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
        $_POST['orderId'] = 200022;
        $_POST['userID'] = 5;
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