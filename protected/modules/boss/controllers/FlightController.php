<?php
class FlightController extends BossController {
    public function actionOrderList() {
        $data = FlightCNOrder::search($_GET, True);
        $dataProvider = new CActiveDataProvider('FlightCNOrder', array(
            'criteria' => $data['criteria'],
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        
        $this->setRenderParams('breadCrumbs', array('飞机票', '订单列表'));
        $this->render('orderList', array('dataProvider' => $dataProvider, 'params' => $data['params']));
    }
}