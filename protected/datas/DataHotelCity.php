<?php
class DataHotelCity {
    public static function getCities() {
        $cacheKey = KeyManager::getHotelCitiesKey();
        if (!($hotelCitys = Yii::app()->cache->get($cacheKey))) {
            $hotelCitys = array();
            /*
             array (
                     'cityCode' => 'BJS',
                     'cityName' => '北京',
                     'citySpell' => 'beijing',
                     'cityShortSpell' => 'bj',
                     'firstChar' => 'B',
             ),
            */
            $xmlString= file_get_contents(Q::getDataDocFile('hotelCity.xml'));
            $cityXml = simplexml_load_string($xmlString);
            $countrys = (array) $cityXml->Data->Countrys;
            foreach ($countrys as $country){
                 foreach ($country as $countryItem){
                     $countryItem = (array) $countryItem;
                     //大陆内
                     if(!in_array($countryItem['@attributes']['CountryId'], array('0001','0002','0003','0004'))) break;
                     if(isset($countryItem['Province'])){
                        foreach ($countryItem['Province'] as $province){
                            $province = (array) $province;
                            if(isset($province['City'])){
                                $province['City'] = (array) $province['City'];
                                foreach ($province['City'] as $city){
                                    $city = (array) $city;
                                    if(isset($city['CityId'])){
                                        $hotelCitys[$city['CityId']] = self::rendCity($city);
                                    }
                                    if(isset($city['@attributes']) && isset($city['@attributes']['CityId'])){
                                        $hotelCitys[$city['@attributes']['CityId']] = self::rendCity($city);
                                    }
                                }
                            }else {
                               if(isset($province['@attributes']) && isset($province['@attributes']['CityId'])){
                                        $hotelCitys[$province['@attributes']['CityId']] = self::rendCity($province);
                               }
                            }
                        }
                     }
                 }
            }
            Yii::app()->cache->set($cacheKey, $hotelCitys);
        }
        return $hotelCitys;
    }
    
    public static function rendCity($city){
        $cityId = isset($city['CityId']) ? $city['CityId'] : $city['@attributes']['CityId'];
        $cityName = isset($city['CityName']) ? $city['CityName'] : $city['@attributes']['CityName'];
        $pingyin = CUtf8_PY::encode($cityName, 'all');
        $firstChar = $pingyin ? strtoupper(substr($pingyin, 0, 1)) : '';
        $cityShortSpell = '';
        if($pingyin){
            $pingyinArray = explode(' ', $pingyin);
            foreach ($pingyinArray as $pingyinArrayItem) {
                $cityShortSpell .= substr($pingyinArrayItem, 0, 1);
            }
        }
        $pingyin = str_replace(' ', '', $pingyin);
        return array('cityCode'=>$cityId, 'cityName'=>$cityName, 'citySpell'=>$pingyin, 'cityShortSpell'=>$cityShortSpell, 'firstChar'=>$firstChar);
    }
    
}
?>