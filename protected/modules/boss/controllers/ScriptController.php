<?php
class ScriptController extends BossController {
    
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
            //if(Hotel::model()->find("cityId='{{$city['cityCode']}}'")) continue;
            //$res = ProviderCNBOOKING::request('HotelSearch', array('CountryId'=>'0001','ProvinceId'=>'3100','CityId'=>'3301')); //南京
            $res = ProviderCNBOOKING::request('HotelSearch', array('CountryId'=>$city['CountryId'],'ProvinceId'=>$city['ProvinceId'],'CityId'=>$city['cityCode']));
            if(F::isCorrect($res) && $res['data']){
                if(is_array($res['data']['Hotels'])){
                    $hotels = $res['data']['Hotels'];
                    if(isset($hotels['Hotel']['HotelId'])) Hotel::_updateHotel($hotels['Hotel']); //!!!只有一个时直接就是Hotel
                    else 
                        foreach ($hotels['Hotel'] as $key => $hotel){
                            Hotel::_updateHotel($hotel);
                            if($i>3) break;
                            $i++;
                        }
                }else Q::log($res, 'Hotel._UpdateHotel.Error.Hotel');
                
            }
        }
    }
    
    
}