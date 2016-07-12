<?php
class HotelCLICommand extends CConsoleCommand {
    
    
    //更新所有酒店(遍历城市) /dragon/bin/php-5.3.28/bin/php /dragon/webapp/tmc/ScriptCLI.php HotelCLI updatehotels
    public function actionUpdateHotels() {
        ini_set('memory_limit', '1024M');
        ignore_user_abort(); //忽略用户影响
        set_time_limit(0); //连续运行
        $cityList = DataHotelCity::getCities();
        $res = $hotels = array();
        //Q::log($cityList, 'Hotel._UpdateHotel.$cityList');
        foreach ($cityList as $city) {
            echo "{$city['cityCode']}  "." \n";
            //if($city['cityCode']=='121000001'||$city['cityCode']<2503) continue;
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
                      }else Q::realtimeLog($res, 'Hotel.HotelSearch.UpdateHotels.Hotels.None');
                 }else $searchEnd = true;
            }
            echo "OK \n";
        }
    }
    
    // D:/xampp/php/php.exe D:/www/TMC/ScriptCLI.php HotelCLI UpdateHotelPrice
    public function actionUpdateHotelPrice() {
        ignore_user_abort(); //忽略用户影响
        set_time_limit(0); //连续运行
        $time = time() - 24 * 3600;
        $hotelsARs = Hotel::model() ->findAll(array(
                                'select' => array( 'hotelId'),
                                'condition' => "utime < {$time}",
                                'limit'=>10,
                        ));
/*         $hotels = array();
        $hotelObj = new stdClass;
        $hotelObj->hotelId = '1';
        foreach ($hotelsAR as $hotel) {
            $hotelObj->hotelId = $hotel->hotelId ;
            $hotels[] = $hotelObj;
        } */
        echo 'go!----------'.date('m-d H:i:s').'----------';
        $allLowPrice = Hotel::getAllLowPrice($hotelsARs, array('checkIn'=>date('Y-m-d'), 'checkOut'=>date('Y-m-d', strtotime('+1 day'))));
        foreach ($hotelsARs as $hotel) {
            $hotel->updateByPk($hotel->getPrimaryKey(), array('utime'=>time()));
        }
        Q::realtimeLog($allLowPrice, 'Hotel.UpdateHotelPrice');
        var_dump($allLowPrice);
        echo 'end----------'.date('m-d H:i:s').'----------'."\n"."\n";
    }
    
}