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
                        'HotelId' => $_GET['hotelId'],
                        'RoomId' => $_GET['roomId'],
                        'RateplanId' => $_GET['rateplanId'],
                        'CheckIn' => $_GET['checkIn'],
                        'CheckOut' => $_GET['checkOut'],
                        'RoomCount' => 1,
                        'OrderAmount' => $_GET['orderAmount'],
                ))) && $res['data']){
            if(is_array($res['data']) && $res['data']['ReturnCode'] == ProviderCNBOOKING::PREBOOKINGCHECK_SUCCESS){
                $this->corAjax();
            }else $this->errAjax(RC::RC_H_HOTEL_PREBOOKINGCHECK_ERROR);
        }
    }
    
    public function actionBooking() {
/*      $_POST =  array(
                    'hotelId' => 1106,
                    'roomId' => 127663,
                    'rateplanId' => 1184,
                    'checkIn' => '2016-07-20',
                    'checkOut' => '2016-07-22',
                    'roomCount' => 2,
                    'orderAmount'=>2240,
                    'bookName' => '测试_王东',
                    'bookPhone' => '15952016956',
                    'guestName' => '测试_王本',
                    'reason'=>'去上海',
                    'specialRemark'=>'要干净明亮',
                    'lastCancelTime'=>''
            );  */
        $order = new HotelOrder();
        $order->attributes = $_POST;
        isset($_POST['lastCancelTime']) && $_POST['lastCancelTime'] && $order->lastCancelTime = date('Y-m-d H:i:s', strtotime($_POST['lastCancelTime']));
        if($order->save()){
            if(F::isCorrect($res= ProviderCNBOOKING::request('Booking',
                    array(
                            'HotelId' => $_POST['hotelId'],
                            'RoomId' => $_POST['roomId'],
                            'RateplanId' => $_POST['rateplanId'],
                            'CheckIn' => $_POST['checkIn'],
                            'CheckOut' => $_POST['checkOut'],
                            'RoomCount' => $_POST['roomCount'],
                            'OrderAmount' => $_POST['orderAmount'],
                            'BookName' => $_POST['bookName'],
                            'BookPhone' => $_POST['bookPhone'],
                            'GuestName' => $_POST['guestName'],
                            'SpecialRemark' => isset($params['specialRemark']) ? $params['specialRemark'] : '',
                            'CustomerOrderId' => $order->id,
                    ))) && $res['data']){
                if(is_array($res['data']) && $res['data']['ReturnCode'] == ProviderCNBOOKING::BOOKING_SUCCESS){
                        $status = 8;
                        if($res['data']['Order']['OrderStatusId']<10) { // 订单状态大于9都是已确认 mail报警! }
                            Q::log($res['data']['Order'], 'Api.Hotel.actionBooking.OrderStatusId.error');
                            $status = 9;
                        }
                        if($order->updateByPk($order->getPrimaryKey(), array('status'=>$status, 'oID'=>$res['data']['Order']['OrderId']))){
                            $this->corAjax(array('orderId'=>$order->id));
                        }else $this->errAjax(RC::RC_DB_ERROR);
                }else $this->errAjax($res['data']['ReturnCode'], $res['data']['ReturnMessage']);
            }
        }else {
            Q::log($order->getErrors(), 'dberror.hotel.booking');
            $this->errAjax(RC::RC_DB_ERROR);
        }
    }
    
}