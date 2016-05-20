<?php
class FlightController extends ApiController {
    public function actionCityList() {
        $rtn = array('cityList' => array(), 'hotList' => array());
        $cityList = ProviderF::getCNCityList();
        foreach ($cityList as &$city) {
            $city['firstChar'] = $firstChar = strtoupper($city['citySpell']{0});
            if (!isset($rtn['cityList'][$firstChar])) {
                $rtn['cityList'][$firstChar] = array();
            }
            
            $rtn['cityList'][$firstChar][] = $city;
        }
        
        $rtn['hotList'] = array_values(F::arrayGetByKeys($cityList, array('BJS', 'SHA', 'CAN')));
        
        $this->corAjax($rtn);
    }
    
    public function actionFlightList() {
        $res = ProviderF::getCNFlightList($_GET);
        $rtn = F::isCorrect($res) ? $res['data'] : array();
        
        $this->corAjax($rtn);
    }
    
    public function actionFlightDetail() {
        $this->onAjax(ProviderF::getCNFlightDetail($_GET));
    }
    
    public function actionBook() {
        $this->onAjax(FlightCNOrder::createOrder($_POST));
    }
    
    public function actionOrderList() {
        
    }
    
    public function actionOrderDetail() {
        
    }
}