<?php
class TrainController extends BossController {
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
        
        $data = TrainOrder::search($searchParams, True);
        $dataProvider = new CActiveDataProvider('TrainOrder', array(
            'criteria' => $data['criteria'],
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        
        $this->setRenderParams('breadCrumbs', array('火车票', '订单列表'));
        $this->render('orderList', array('dataProvider' => $dataProvider, 'params' => $data['params'], 'searchTypes' => $searchTypes));
    }
    
    public function actionChangeStatus() {
        if (!F::checkParams($_POST, array('orderID' => ParamsFormat::INTNZ, 'status' => ParamsFormat::F_STATUS))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($order = TrainOrder::model()->findByPk($_POST['orderID']))) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $_POST['operaterID'] = $this->admin->id;
        $this->onAjax($order->changeStatus($_POST['status'], $_POST));
    }
    
    public function actionGetOrderDetailHtml() {
        if (!F::checkParams($_GET, array('orderID' => ParamsFormat::INT)) || !($order = TrainOrder::model()->findByPk($_GET['orderID']))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $finances = CompanyFinanceLog::model()->findAllByAttributes(array('orderID' => $order->id), array('order' => 'id DESC'));
        $logs = Log::model()->findAllByAttributes(array('orderID' => $order->id, 'type' => Dict::BUSINESS_FLIGHT), array('order' => 'id DESC'));
        
        $rtn = array('html' => $this->renderPartial('_orderDetail', array('order' => $order, 'finances' => $finances, 'logs' => $logs), True));
        $this->corAjax($rtn);
    }
}