<?php
class Hotel extends QActiveRecord {
/*     public static $priceSelect = array(200=>array(0, 200), 400=>array(200, 400), 600=>array(400, 600), 800=>array(800, 80000));
    
    public static function isPriceSelect($select){
        return isset(self::$priceSelect[$select]);
    } */
    
    static  $starArray = array('012012'=>array('012009', '012011', '012012'), '012014'=>array('012013', '012014'), '012016'=>array('012015', '012016'), '012018'=>array('012017', '012018','012019', '012020'), );
        
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
                }else Q::log($res, 'Hotel.HotelSearch.getHotelFromCity.Hotels.None');
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
                Q::log($hotelInput['HotelName'], 'Hotel._UpdateHotel.HotelName.Error.'.$hotelInput['HotelId']);
            }
            if(is_array($hotelInput['Address'])) {
                $hotelInput['Address'] = isset($hotelInput['Address'][0]) ? $hotelInput['Address'][0] : '';
                Q::log($hotelInput['Address'], 'Hotel._UpdateHotel.Address.Error.'.$hotelInput['HotelId']);
            }
            if(is_array($hotelInput['Reserve2'])) {
                $hotelInput['telephone'] = isset($hotelInput['Reserve2'][0]) ? $hotelInput['Reserve2'][0] : '';
                Q::log($hotelInput['Reserve2'], 'Hotel._UpdateHotel.Reserve2.Error.'.$hotelInput['HotelId']);
            }else $hotelInput['telephone'] = $hotelInput['Reserve2'];
            if(is_array($hotelInput['Intro'])) {
                $hotelInput['Intro'] = isset($hotelInput['Intro'][0]) ? $hotelInput['Intro'][0] : '';
                Q::log($hotelInput['Intro'], 'Hotel._UpdateHotel.Intro.Error.'.$hotelInput['HotelId']);
            }
            foreach ($hotelInput as $key => $value) {
                $hotelInput[lcfirst($key)] = $value;
            }
            $hotelInput['lon'] = floatval($hotelInput['lon']);
            $hotelInput['lat'] = floatval($hotelInput['lat']);
        
            $hotel = Hotel::model()->findByPk($hotelInput['HotelId']);
            //if($hotel) return true;
            $trans = Yii::app()->db->beginTransaction();
            try {
                $hotel  = $hotel ? $hotel : new Hotel();
                $hotel->attributes = $hotelInput;
                if(!$hotel->save()){
                    Q::realtimeLog(json_encode($hotel->attributes).json_encode($hotel->getErrors()), 'Hotel.saveDB.Error');
                }else {
                    self::setImages($hotel, $hotelInput['Reserve1']);
                    self::setLandmarks($hotel, $hotelInput['Landmarks']);
                    Q::realtimeLog($hotel->hotelId, 'Hotel.saveDB.OK');
                    $return = true;
                }
                $trans->commit();
            } catch (Exception $e) {
                Q::log($e->getMessage(), 'dberror.changeStatus');
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
        if(isset($landmarks['Landmark']) && $landmarks['Landmark']){
            if(is_array($landmarks['Landmark'])){
                foreach ($landmarks['Landmark'] as $key => $value) {
                    if(in_array($value['LandName'], array('机场', '地铁', '高速公路', '火车站', '火车站', '公交车站', '会展中心'))) unset($landmarks['Landmark'][$key]);
                }
                HotelLandmark::model()->deleteAll("hotelId={$hotel->hotelId}");
                $landmarks = $landmarks['Landmark'];
                foreach ($landmarks as $landmark) {
                    $hotelLandmark = new HotelLandmark();
                    $hotelLandmark->attributes = $landmark;
                    $hotelLandmark->hotelId = $hotel->hotelId;
                    $hotelLandmark->save();
                }
            }else Q::log($landmarks['Landmark'], 'Hotel..setLandmarks.Error');
        }
        return $return;
    }
    
    
}