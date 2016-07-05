<?php
class HotelController extends AdminController {
   public function actionOrderList() {
        $searchTypes = array(
                'orderID' => '订单ID',
                'userID' => '用户ID',
                'companyID' => '企业ID',
                'operaterID' => '客服ID'
        );
        $searchParams = $_GET;
        $searchParams['searchType'] = !empty($searchParams['searchType']) && isset($searchTypes[$searchParams['searchType']]) ? $searchParams['searchType'] : False;
        if ($searchParams['searchType']) {
            $searchParams[$searchParams['searchType']] = empty($searchParams['searchValue']) ? '' : $searchParams['searchValue'];
        }
        $searchParams['status'] = empty($searchParams['status']) ? array() : array($searchParams['status']);
        
        $data = HotelOrder::search($searchParams, True);
        $dataProvider = new CActiveDataProvider('HotelOrder', array(
                'criteria' => $data['criteria'],
                'pagination' => array(
                        'pageSize' => 10,
                )
        ));
        
        $this->setRenderParams('breadCrumbs', array('酒店', '订单列表'));
        $this->render('orderList', array('dataProvider' => $dataProvider, 'params' => $data['params'], 'searchTypes' => $searchTypes));
        
    }
    
    public function actionGetOrderDetailHtml(){
        if (!F::checkParams($_GET, array('orderID' => ParamsFormat::INT)) || !($order = HotelOrder::model()->findByPk($_GET['orderID']))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        $finances = CompanyFinanceLog::model()->findAllByAttributes(array('orderID' => $order->id), array('order' => 'id DESC'));
        $logs = Log::model()->findAllByAttributes(array('orderID' => $order->id, 'type' => Log::TYPE_HOTEL), array('order' => 'id DESC'));
    
        $rtn = array('html' => $this->renderPartial('_orderDetail', array('order' => $order, 'finances' => $finances, 'logs' => $logs), True));
        $this->corAjax($rtn);
    }
    
}