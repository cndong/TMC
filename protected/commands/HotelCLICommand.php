<?php
class HotelCLICommand extends CConsoleCommand {
    //更新所有酒店(遍历城市)
    public function actionUpdateHotels() {
        ignore_user_abort(); //忽略用户影响
        set_time_limit(0); //连续运行
        $cityList = DataHotelCity::getCities();
        //Q::log($cityList, 'Hotel._UpdateHotel.$cityList');
        foreach ($cityList as $city) {
            $i=0;
            echo "{$city['cityCode']}  "." \n";
    
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
                            break 2;
                        }
                    }else Q::log($res, 'Hotel.HotelSearch.UpdateHotels.Hotels.None');
                }else $searchEnd = true;
            }
            echo "OK \n";
        }
    }
}