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
        if (!F::isCorrect($res = ProviderT::getTrainList($_GET))) {
            $this->onAjax($res);
        }
        
        $rtn = array();
        foreach ($res['data'] as $trainInfo) {
            $trainInfo['seats'] = array_values($trainInfo['seats']);
            $rtn[] = $trainInfo;
        }
        
        $this->corAjax(array('trainList' => $rtn, 'insurePrice' => DictTrain::INSURE_PRICE));
    }
    
    public function actionStopList() {
        if (!F::isCorrect($res = ProviderT::getStopList($_GET))) {
            $this->onAjax($res);
        }
        
        $this->corAjax(array('stopList' => $res['data']));
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