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
}