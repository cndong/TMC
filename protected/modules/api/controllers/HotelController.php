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
 /*        $res = ProviderF::getCNFlightList($_GET);
        $rtn = F::isCorrect($res) ? $res['data'] : array();
    
        $this->corAjax($rtn); */
    }
    
    public function actionHotelSearch() {
        $hotel = ProviderCNBOOKING::request('HotelSearch', array('CountryId'=>'0001','ProvinceId'=>'1100','CityId'=>'1101'));
        $this->corAjax($hotel);
    }
    
    public function actionRoomSearch() {
        $hotel = ProviderCNBOOKING::request('RoomSearch', array('CountryId'=>'0001','ProvinceId'=>'1100','CityId'=>'1101', 'HotelId'=>6843));
        $this->corAjax($hotel);
    }
    
}