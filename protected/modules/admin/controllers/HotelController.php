<?php
class HotelController extends AdminController {
    public function actionOrderList() {
    }
    
    //根据城市更新酒店
    public function actionUpdateHotel() {
        $okHtml = $faileHtml = '';
        set_time_limit(0);
        $cityList = DataHotelCity::getCities();
        foreach ($cityList as $city) {
            if(Hotel::model()->find("cityId='{{$city['cityCode']}}'")) continue;
            //$res = ProviderCNBOOKING::request('HotelSearch', array('CountryId'=>'0001','ProvinceId'=>'1100','CityId'=>'1101')); break; //南京
            $res = ProviderCNBOOKING::request('HotelSearch', array('CountryId'=>$city['CountryId'],'ProvinceId'=>$city['ProvinceId'],'CityId'=>$city['cityCode']));
            if(F::isCorrect($res) && $res['data']){
                if(is_array($res['data']['Hotels'])){
                    $hotels = $res['data']['Hotels'];
                    if(isset($hotels['Hotel']['HotelId'])) $this->_UpdateHotel($hotels['Hotel']); //!!!只有一个时直接就是Hotel
                    else 
                        foreach ($hotels['Hotel'] as $key => $hotel) {
                            $this->_UpdateHotel($hotel);
                            //$faileHtml.="{$city['cityCode']}, ";
                            break;
                        }
                }else Q::log($res, 'Hotel._UpdateHotel.Error.Hotel');
            }
        }
    }
    
    
    private  function _setImages($images) {
        $return = '';
        if(isset($images['Images']) && $images['Images'] && isset($images['Images']['Image'])){
            $return = json_encode(array_slice($images['Images']['Image'], 0, 5));
        }
        return $return;
    }
    
    private  function _setLandmarks($landmarks) {
        $return = '';
        if(isset($landmarks['Landmark']) && $landmarks['Landmark']){
            if(is_array($landmarks['Landmark'])){
                foreach ($landmarks['Landmark'] as $key => $value) {
                    if(in_array($value['LandName'], array('机场', '地铁', '高速公路', '火车站', '火车站', '公交车站', '会展中心'))) unset($landmarks['Landmark'][$key]);
                }
                $return = json_encode($landmarks['Landmark']);
            }else Q::log($landmarks['Landmark'], 'Hotel._UpdateHotel.Error');
        }
        return $return;
    }
    
    private  function _UpdateHotel($hotelInput) {
        if(is_array($hotelInput['Reserve2'])) {
            $hotelInput['telephone'] = isset($hotelInput['Reserve2'][0]) ? $hotelInput['Reserve2'][0] : '';
            Q::log($hotelInput['Reserve2'], 'Hotel._UpdateHotel.Reserve2.Error.'.$hotelInput['HotelId']);
        }else $hotelInput['telephone'] = $hotelInput['Reserve2'];
        if(is_array($hotelInput['Intro'])) {
            $hotelInput['Intro'] = isset($hotelInput['Intro'][0]) ? $hotelInput['Intro'][0] : '';
            Q::log($hotelInput['Intro'], 'Hotel._UpdateHotel.Intro.Error.'.$hotelInput['HotelId']);
        }
        $hotelInput['images'] = $this->_setImages($hotelInput['Reserve1']);
        $hotelInput['landmarks'] = $this->_setLandmarks($hotelInput['Landmarks']);
        foreach ($hotelInput as $key => $value) {
            $hotelInput[lcfirst($key)] = $value;
        }
        $hotelInput['lon'] = floatval($hotelInput['lon']);
        $hotelInput['lat'] = floatval($hotelInput['lat']);
        
        $hotel = Hotel::model()->findByPk($hotelInput['HotelId']);
        if($hotel) return true;
        $hotel  = $hotel ? $hotel : new Hotel();
        $hotel->attributes = $hotelInput;
        if(!$hotel->save()){
            Q::log(json_encode($hotel->attributes).json_encode($hotel->getErrors()), 'Hotel._UpdateHotel.Save.Error.');
            return true;
        }else return false;
    }
}