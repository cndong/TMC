<?php
class FlightController extends ApiController {
    public function actionCityList() {
        $this->corAjax(array('cityList' => ProviderF::getCNCityList()));
    }
    
    public function actionFlightList() {
        //可以考虑增加 orderParams，base_64编码下单所需数据，给客户端下单使用
        $res = ProviderF::getCNFlightList($_GET);
        $rtn = F::isCorrect($res) ? $res['data'] : array();
        
        $this->corAjax(array('flightList' => $rtn));
    }
    
    public function actionFlightDetail() {
        $res = ProviderF::getCNFlightDetail($_GET);
    }
    
    public function actionBook() {
        $this->onAjax(FlightCNOrder::createOrder($_POST));
    }
    
    public function actionOrderList() {
        
    }
    
    public function actionOrderDetail() {
        
    }
}