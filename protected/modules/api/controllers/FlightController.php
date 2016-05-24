<?php
class FlightController extends ApiController {
    public function actionCityList() {
        $rtn = array('cityList' => array(), 'hotList' => array());
        $cityList = ProviderF::getCNCityList();
        foreach ($cityList as &$city) {
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
        if (!($params = F::checkParams($_POST, array_fill_keys(array('contacter', 'departRoute', 'passengers', 'price'), ParamsFormat::JSON)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        foreach ($params as $k => $v) {
            $_POST[$k] = json_decode($v);
        }
        
        if (!($params = F::checkParams($_POST, array('returnRoute' => '!' . ParamsFormat::JSON . '--', 'invoiceAddress' => '!' . ParamsFormat::JSON . '--')))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        foreach ($params as $k => $v) {
            $_POST[$k] = json_decode($v);
        }
        
        $this->onAjax(FlightCNOrder::createOrder($_POST));
    }
    
    public function actionOrderList() {
        
    }
    
    public function actionOrderDetail() {
        
    }
}