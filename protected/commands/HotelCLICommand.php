<?php
class HotelCLICommand extends CConsoleCommand {
    
    
    //更新所有酒店(遍历城市) /dragon/bin/php-5.3.28/bin/php /dragon/webapp/tmc/ScriptCLI.php HotelCLI updatehotels
    public function actionUpdateHotels() {
        ignore_user_abort(); //忽略用户影响
        set_time_limit(0); //连续运行
        $cityList = DataHotelCity::getCities();
        $res;
        //Q::log($cityList, 'Hotel._UpdateHotel.$cityList');
        foreach ($cityList as $city) {
            echo "{$city['cityCode']}  "." \n";
            //if($city['cityCode']<1701) continue;
            $searchEnd = false;
            for($pageNo=1; $pageNo<100; $pageNo++){
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
            
            echo "OK \n";
        }
    }
}