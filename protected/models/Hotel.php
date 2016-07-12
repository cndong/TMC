<?php
class Hotel extends QActiveRecord {
    /*     public static $priceSelect = array(200=>array(0, 200), 400=>array(200, 400), 600=>array(400, 600), 800=>array(800, 80000));
        
        public static function isPriceSelect($select){
            return isset(self::$priceSelect[$select]);
        } */

    static $starArray = array(
            '012012' => array(
                '012009', '012011', '012012'
            ),
            '012014' => array(
                '012013', '012014'
            ),
            '012016' => array(
                '012015', '012016'
            ),
            '012018' => array(
                '012017', '012018', '012019', '012020'
            ),
    );

    static $bedLimitArray = array(
        '045001' => '大床/双床', '045002' => '大床', '045003' => '双床',
    );

    static $breakfastArray = array(
            '011001' => '不含早餐',
            '011002' => '单早',
            '011003' => '双早',
            '011004' => '单双早',
            '011007' => '床位早',
            '011008' => '三早',
            '011010' => '六早',
            '011011' => '四早',
            '011012' => '八早',
    );
    
    static $cityLowPriceKeyPrefix = 'CityLowPrice';
    static $lowPriceKeyPrefix = 'LowPrice';
    
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return '{{hotel}}';
    }
    
    public function rules() {
        return array(
            array('hotelId, ctime, utime', 'numerical', 'integerOnly' => True),
            array('hotelName, telephone', 'length', 'max' => 64),
            array('countryId, provinceId, cityId', 'length', 'max' => 4),
            array('address, image', 'length', 'max' => 255),
            array('star, recommendedLevel', 'length', 'max' => 6),
            array('lon, lat', 'numerical'),
            array('intro', 'length', 'max' => 1024),
        );
    }
    
    static function load($hotelId, $cityId=''){
        $hotel = Hotel::model()->findByPK($hoteId);
        return $hotel ? $hotel : self::getHotelFromCity($hotelId, $cityId);
    }
    
    //获取酒店用城市
    static function getHotelFromCity($hotelId, $cityId) {
        if(!$cityId) return false;
        $city = DataHotelCity::getCity($cityId);
        
        $searchSuccess = false;
        $searchEnd = false;
        for($pageNo=1; $pageNo<50; $pageNo++){
            if($searchSuccess || $searchEnd) break;
            $res = ProviderCNBOOKING::request('HotelSearch', array('CountryId'=>$city['CountryId'],'ProvinceId'=>$city['ProvinceId'],'CityId'=>$city['cityCode']), array('DisplayReq'=>30, 'PageNo'=>$pageNo, 'PageItems'=>200));
            if(F::isCorrect($res) && is_array($res['data'])){
                if(is_array($res['data']['Hotels'])){
                    $hotels = $res['data']['Hotels'];
                    if($hotels['HotelCount'] == 1) $hotels['Hotel'] = array($hotels['Hotel']);
                    foreach ($hotels['Hotel'] as $key => $hotel){
                        if($hotel['HotelId'] == $hotelId) {
                            Q::realtimeLog($hotel['HotelId'], $city['cityCode']);
                            Hotel::saveDB($hotel);
                            $searchSuccess = true;
                            break;
                        }
                    }
                }else Q::realtimeLog($res, 'Hotel.HotelSearch.getHotelFromCity.Hotels.None');
            }else $searchEnd = true;
        }
    }
    
    //更新已存在单个酒店
    static function updateHotel($hoteId) {
        $return = false;
        $hotel = Hotel::model()->findByPK($hoteId);
        if(!$hotel) $return = false;
        $city = DataHotelCity::getCity($hotel['cityId']);
        if(F::isCorrect($res=ProviderCNBOOKING::request('HotelSearch', array('CountryId'=>$city['CountryId'],'ProvinceId'=>$city['ProvinceId'],'CityId'=>$city['cityCode'],'HotelId'=>$hoteId)))){
            if(is_array($res['data']['Hotels'])){
                $hotels = $res['data']['Hotels'];
                if(isset($hotels['Hotel']['HotelId'])) $return = self::saveDB($hotels['Hotel']); //!!!只有一个时直接就是Hotel
            }
        }
        return $return;
    }
    
    static  function saveDB($hotelInput) {
            $return = false;
            if(is_array($hotelInput['HotelName'])) {
                $hotelInput['HotelName'] = isset($hotelInput['HotelName'][0]) ? $hotelInput['HotelName'][0] : '';
                Q::realtimeLog($hotelInput['HotelName'], 'Hotel._UpdateHotel.HotelName.Error.'.$hotelInput['HotelId']);
            }
            if(is_array($hotelInput['Address'])) {
                $hotelInput['Address'] = isset($hotelInput['Address'][0]) ? $hotelInput['Address'][0] : '';
                Q::realtimeLog($hotelInput['Address'], 'Hotel._UpdateHotel.Address.Error.'.$hotelInput['HotelId']);
            }
            if(is_array($hotelInput['Reserve2'])) {
                $hotelInput['telephone'] = isset($hotelInput['Reserve2'][0]) ? $hotelInput['Reserve2'][0] : '';
                Q::realtimeLog($hotelInput['Reserve2'], 'Hotel._UpdateHotel.Reserve2.Error.'.$hotelInput['HotelId']);
            }else $hotelInput['telephone'] = $hotelInput['Reserve2'];
            if(is_array($hotelInput['Intro'])) {
                $hotelInput['Intro'] = isset($hotelInput['Intro'][0]) ? $hotelInput['Intro'][0] : '';
                Q::realtimeLog($hotelInput['Intro'], 'Hotel._UpdateHotel.Intro.Error.'.$hotelInput['HotelId']);
            }
            if(isset($hotelInput['Email'])) unset($hotelInput['Email']);
            if(isset($hotelInput['PostCode'])) unset($hotelInput['PostCode']);
            if(isset($hotelInput['Guide'])) unset($hotelInput['Guide']);
            if(isset($hotelInput['StartBusinessDate'])) unset($hotelInput['StartBusinessDate']);
            if(isset($hotelInput['Repairdate'])) unset($hotelInput['Repairdate']);
            
            foreach ($hotelInput as $key => $value) {
                if($key!='Reserve1' && $key!='Landmarks' && !is_string($value)){
                    Q::realtimeLog(json_encode($value), 'Hotel.saveDB.Error.array_value.'.$hotelInput['HotelId'].'.'.$key);
                    return $return;
                }
                $hotelInput[lcfirst($key)] = $value;
            }
            $hotelInput['lon'] = floatval($hotelInput['lon']);
            $hotelInput['lat'] = floatval($hotelInput['lat']);
        
            $hotel = Hotel::model()->findByPk($hotelInput['HotelId']);
            if($hotel) return true;
            $trans = Yii::app()->db->beginTransaction();
            try {
                $hotel  = $hotel ? $hotel : new Hotel();
                $hotel->attributes = $hotelInput;
                if(!$hotel->save()){
                    Q::realtimeLog(json_encode($hotel->attributes).json_encode($hotel->getErrors()), "Hotel.{$hotelInput['HotelId']}.saveDB.Error");
                }else {
                    self::setImages($hotel, $hotelInput['Reserve1']);
                    self::setLandmarks($hotel, $hotelInput['Landmarks']);
                    Q::realtimeLog($hotel->hotelId, "Hotel.{$hotelInput['HotelId']}.saveDB.OK");
                    $return = true;
                }
                $trans->commit();
            } catch (Exception $e) {
                Q::realtimeLog($e->getMessage(), "Hotel.{$hotelInput['HotelId']}.saveDB.Tranerror");
                $trans->rollback();
            }
            return $return;
    }
    
    static  function setImages($hotel, $images) {
        $return = '';
        if(isset($images['Images']) && $images['Images'] && isset($images['Images']['Image'])){
            $images =array_slice($images['Images']['Image'], 0, 6);
            HotelImage::model()->deleteAll("hotelId={$hotel->hotelId}");
            foreach ($images as $image) {
                $hotelImage = new HotelImage();
                if(isset($image['ImageName']) && isset($image['ImageUrl']) ){
                    is_array($image['ImageName']) && $image['ImageName']= '';
                    $hotelImage->attributes = $image;
                    $hotelImage->hotelId = $hotel->hotelId;
                    $hotelImage->save();
                }
            }
        }
    }
    
    static  function setLandmarks($hotel, $landmarks) {
        $return = '';
        if(isset($landmarks['Landmark']) && $landmarks['LandmarkCount']>0){
            if(is_array($landmarks['Landmark'])){
                foreach ($landmarks['Landmark'] as $key => $value) {
                    if(in_array($value['LandName'], array('市中心', '机场', '地铁', '高速公路', '火车站', '火车站', '公交车站', '会展中心'))) unset($landmarks['Landmark'][$key]);
                }
                HotelLandmark::model()->deleteAll("hotelId={$hotel->hotelId}");
                $landmarks = $landmarks['Landmark'];
                foreach ($landmarks as $landmark) {
                    $hotelLandmark = new HotelLandmark();
                    $hotelLandmark->attributes = $landmark;
                    $hotelLandmark->hotelId = $hotel->hotelId;
                    $hotelLandmark->save();
                }
            }else Q::realtimeLog($landmarks['Landmark'], 'Hotel.setLandmarks.Error');
        }
        return $return;
    }
    
   //$params = array('checkIn'=>'2016-07-20', 'checkOut'=>'2016-07-21',  'cityId' => '0101')
    static function getLowPrice($hotelId, $params){
        $cacheKey = self::$lowPriceKeyPrefix.$hotelId.$params['checkIn'].$params['checkOut'];
        if($priceArray = Yii::app()->cache->get($cacheKey)){
        }else if(isset($params['cityId'])){
            if($priceCityArray = self::getCityLowPrice($params['cityId'])){
                if(isset($priceCityArray[$hotelId])){
                    $priceArray = $priceCityArray[$hotelId];
                }
            }
        }
        return $priceArray;
    }
    
    static function setLowPrice($hotelId, $params, $priceArray, $fuzzy = true){
        $cacheKey = self::$lowPriceKeyPrefix.$hotelId.$params['checkIn'].$params['checkOut'];
        Yii::app()->cache->set($cacheKey, $priceArray, 48*3600);
        if(isset($params['cityId']) && $fuzzy){
            if($priceCityArray = self::getCityLowPrice($params['cityId'])){
                $priceCityArray[$hotelId] = $priceArray;
            }else $priceCityArray = array($hotelId=>$priceArray);
            self::setCityLowPrice($params['cityId'], $priceCityArray);
        }
    }
    
    static function getCityLowPrice($cityId){
        $cacheKey = self::$cityLowPriceKeyPrefix.$cityId;
        return Yii::app()->cache->get($cacheKey);
    }
    
    //设置城市价格缓存
    static function setCityLowPrice($cityId, $priceCityArray){
        $cacheKey = self::$cityLowPriceKeyPrefix.$cityId;
        return Yii::app()->cache->set($cacheKey, $priceCityArray);
    }
    
    
    // $fuzzy 模糊查询 只为获得一个缓存的最低价 无需精确到入职/离开日期
    static function getAllLowPrice($hotels, $params){
        $allLowPrice = $postParamsMulti =  $city = $cacheCitys= array();
        foreach ($hotels as $hotel) {
            $city = DataHotelCity::getCity($hotel->cityId);
            $cacheCitys[$hotel->hotelId] = $city['cityCode'];
            if (($priceArray = self::getLowPrice($hotel->hotelId, array_merge($params, array('cityId' => $cacheCitys[$hotel->hotelId])))) === false) {
                $postParamsMulti[$hotel->hotelId] = array('xmlRequest'=>ProviderCNBOOKING::getRequestXML('RatePlanSearch', array(
                        'CountryId' => $city['CountryId'],
                        'ProvinceId' => $city['ProvinceId'],
                        'CityId' => $city['cityCode'],
                        'HotelId' => $hotel->hotelId,
                        'CheckIn' => $params['checkIn'],
                        'CheckOut' => $params['checkOut']
                ), $scrollingInfo = array('DisplayReq'=>40, 'PageNo'=>1, 'PageItems'=>'50')));
            }else $allLowPrice[$hotel->hotelId] = $priceArray ? $priceArray[0] : 0;
        }
    
        $results =ProviderCNBOOKING::multiRequest($postParamsMulti);
        foreach ($results as $hotelId => $res) {
            $priceArray = array();
            if(F::isCorrect($res) && $res['data']){
                if(is_array($res['data']['Hotels']) && isset($res['data']['Hotels']['Hotel']['Rooms']) && is_array($res['data']['Hotels']['Hotel']['Rooms']['Room'])){
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
                                    if(!Q::isProductEnv()) $priceAndStatu['Count'] = rand(0, 10);
                                    if(!Q::isProductEnv()) if(rand(0, 10)>5) $priceAndStatu['LastCancelTime'] = date('Y/n/d G:i:s', time()+3600);
                                    $priceArray[] = $priceAndStatu['Price'];
                                }
                            }
                        }
                    }
    
                }
            }
            sort($priceArray);
            self::setLowPrice($hotelId, array_merge($params, array('cityId'=>$cacheCitys[$hotelId])), $priceArray);
            $allLowPrice[$hotelId] = $priceArray ? $priceArray[0] : 0;
        }
        return $allLowPrice;
    }
    
}