<?php
class FlightController extends AdminController {
    public function actionOrderList() {
        $searchTypes = array(
            'orderID' => '订单ID',
        );
        
        $_GET['companyID'] = $this->admin->company->id;
        $_GET['isPrivate'] = Dict::STATUS_FALSE;
        $searchParams = $_GET;
        $searchParams['searchType'] = !empty($searchParams['searchType']) && isset($searchTypes[$searchParams['searchType']]) ? $searchParams['searchType'] : False;
        if ($searchParams['searchType']) {
            $searchParams[$searchParams['searchType']] = empty($searchParams['searchValue']) ? '' : $searchParams['searchValue'];
        }
        $data = FlightCNOrder::search($searchParams, True);
        $dataProvider = new CActiveDataProvider('FlightCNOrder', array(
            'criteria' => $data['criteria'],
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        
        $this->setRenderParams('breadCrumbs', array('飞机票', '订单列表'));
        $this->render('orderList', array('dataProvider' => $dataProvider, 'params' => $data['params'], 'searchTypes' => $searchTypes));
    }
}