<?php
class ScriptController extends BossController {
    
    public function actionGetHotel($hotelId, $cityId) {
        Hotel::getHotelFromCity($hotelId, $cityId);
    }
    
    public function actionUpdateHotel($hoteId) {
        Hotel::updateHotel($hoteId);
    }
                    
    //更新所有酒店(遍历城市)
    public function actionUpdateHotels() {
        print str_repeat(" ", 4096); //php.ini output_buffering默认是4069字符或者更大，即输出内容必须达到4069字符服务器才会flush刷新输出缓冲
        set_time_limit(0);
        $cityList = DataHotelCity::getCities();
        //Q::log($cityList, 'Hotel._UpdateHotel.$cityList');
        foreach ($cityList as $city) {
            $i=0;
            echo "<br>{$city['cityCode']}  ";
        
            $searchEnd = false;
            for($pageNo=1; $pageNo<50; $pageNo++){
                if($searchEnd) break;
                $res = ProviderCNBOOKING::request('HotelSearch', array('CountryId'=>$city['CountryId'],'ProvinceId'=>$city['ProvinceId'],'CityId'=>$city['cityCode']), array('DisplayReq'=>30, 'PageNo'=>$pageNo, 'PageItems'=>200));
                if(F::isCorrect($res) && is_array($res['data'])){
                    if(is_array($res['data']['Hotels'])){
                        $hotels = $res['data']['Hotels'];
                        if($hotels['HotelCount'] == 1) $hotels['Hotel'] = array($hotels['Hotel']);
                        foreach ($hotels['Hotel'] as $key => $hotel){
                            Q::realtimeLog($hotel['HotelId'], $city['cityCode']);
                            Hotel::saveDB($hotel);
                            //break 2;
                        }
                    }else Q::log($res, 'Hotel.HotelSearch.UpdateHotels.Hotels.None');
                }else $searchEnd = true;
            }
            
        }
    }
    
    
}