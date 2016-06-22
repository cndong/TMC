<?php
class TrainController extends ApiController {
    public function actionStationList() {
        $rtn = array('cityList' => array(), 'hotList' => array());
        $hots = array('beijng', 'shanghai', 'nanjing', 'qinghecheng');
        
        $stations = ProviderT::getStationList();
        ksort($stations);
        foreach ($stations as $station) {
            $firstChar = strtoupper($station['spell']{0});
            if (empty($rtn['cityList'][$firstChar])) {
                $rtn['cityList'][$firstChar] = array('cities' => array(), 'firstChar' => $firstChar);
            }
            
            $rtn['cityList'][$firstChar]['cities'][] = $station;
            if (in_array($station['code'], $hots)) {
                $rtn['hotList'][] = $station;
            }
        }
        
        usort($rtn['cityList'], function($a, $b) {return $a['firstChar'] > $b['firstChar']; });
        
        $this->corAjax($rtn);
    }
    
    public function actionTrainList() {
        if (!F::checkParams($_GET, array('departStationCode' => ParamsFormat::T_STATION_CODE, 'arriveStationCode' => ParamsFormat::T_STATION_CODE, 'departDate' => ParamsFormat::DATE))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $this->corAjax(ProviderT::getTrainList($_GET['departStationCode'], $_GET['arriveStationCode'], $_GET['departDate']));
    }
    
    public function actionStopList() {
        if (!F::checkParams($_GET, array('departStationCode' => ParamsFormat::T_STATION_CODE, 'arriveStationCode' => ParamsFormat::T_STATION_CODE, 'trainNo' => ParamsFormat::T_TRAIN_NO))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $this->corAjax(ProviderT::getStopList($_GET['departStationCode'], $_GET['arriveStationCode'], $_GET['trainNo']));
    }
    
    public function actionBook() {
        if (!($params = F::checkParams($_POST, array_fill_keys(array('contacter', 'departRoute', 'passengers', 'price'), ParamsFormat::JSON)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        foreach ($params as $k => $v) {
            $_POST[$k] = json_decode($v, True);
        }
        
        if (!($params = F::checkParams($_POST, array('returnRoute' => '!' . ParamsFormat::JSON . '--', 'invoiceAddress' => '!' . ParamsFormat::JSON . '--')))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        foreach ($params as $k => $v) {
            $_POST[$k] = json_decode($v, True);
        }
        
        if (!F::isCorrect($res = TrainOrder::createOrder($_POST))) {
            $this->onAjax($res);
        }
        
        $this->corAjax(array('orderID' => $res['data']->id));
    }
}