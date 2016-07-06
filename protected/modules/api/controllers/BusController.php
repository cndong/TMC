<?php
class BusController extends ApiController {
    public function actionFromCities() {
        $rtn = array_values(DataBusCity::getFromCities());
        $this->corAjax(array('fromCities'=>$rtn));
    }
    
    public function actionToCities() {
        if (!$fromCity = Yii::app()->request->getQuery('fromCity', '')) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        $rtn = array_values(DataBusCity::getToCities($fromCity));
        $this->corAjax(array('toCities'=>$rtn));
    }
    
    public function actionBusList() {
        $params = F::getQuery(array('departCity', 'arriveCity', 'departDate'));
        if (empty($params['departCity']) || empty($params['arriveCity']) || !F::isDate($params['departDate'])) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!F::isCorrect($res = Wake::getBusList($params['departCity'], $params['arriveCity'], $params['departDate']))) {
            return $res;
        }
    
        $this->corAjax(array('busList'=>array_values($res['data'])));
    }
}