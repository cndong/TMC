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
    
    public function actionOrderList() {
        $defaultBeginDate = date('Y-m-d', strtotime('-1 month'));
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ, 'beginDate' => '!' . ParamsFormat::DATE . '--' . $defaultBeginDate, 'endDate' => '!' . ParamsFormat::DATE . '--' . Q_DATE)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        $rtn = array();
    
        $stations = ProviderT::getStationList();
        $res = TrainOrder::search($params);
        foreach ($res['data'] as $order) {
            $tmp = F::arrayGetByKeys($order, array('id', 'orderPrice', 'isRound', 'ctime'));
            $tmp['departStation'] = $stations[$order->routes['departRoute']->departStationCode]['name'];
            $tmp['arriveStation'] = $stations[$order->routes['departRoute']->arriveStationCode]['name'];
            $tmp['departTime'] = $order->routes['departRoute']->departTime;
            $tmp['status'] = TrainStatus::getUserDes($order['status']);
            $rtn[] = $tmp;
        }
    
        $this->corAjax(array('orderList' => $rtn));
    }
    
    public function actionReviewOrderList() {
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!($user = User::model()->findByPk($_GET['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
    
        $rtn = array();
        $stations = ProviderT::getStationList();
        if ($user->isReviewer) {
            $res = TrainOrder::search(array('departmentID' => $user->departmentID, 'status' => TrainStatus::$trainStatusGroup['waitCheck']));
            foreach ($res['data'] as $order) {
                $tmp = F::arrayGetByKeys($order, array('id', 'orderPrice', 'isRound', 'ctime'));
                $tmp['departStation'] = $stations[$order->routes['departRoute']['departStationCode']]['name'];
                $tmp['arriveStation'] = $stations[$order->routes['departRoute']['arriveStationCode']]['name'];
                $tmp['departTime'] = $order->routes['departRoute']['departTime'];
                $tmp['status'] = TrainStatus::getUserDes($order['status']);
                $rtn[] = $tmp;
            }
        }
    
        $this->corAjax(array('reviewOrderList' => $rtn));
    }
    
    private function _getFlags($status) {
        return array(
            'isReview' => $status == TrainStatus::WAIT_CHECK,
            'isCancel' => in_array($status, array(TrainStatus::WAIT_CHECK, TrainStatus::CHECK_SUCC, TrainStatus::WAIT_PAY)),
            'isResign' => False, //$status == TrainStatus::BOOK_SUCC,
            'isPay' => $status == TrainStatus::WAIT_PAY,
            'isRefund' => in_array($status, array(TrainStatus::BOOK_SUCC, TrainStatus::RSNED))
        );
    }
    
    public function actionOrderDetail() {
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!($user = User::model()->findByPk($params['userID'])) || !($order = TrainOrder::model()->findByPk($params['orderID'])) || $order->departmentID != $user->departmentID) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
    
        if (($order->userID != $user->id) && (!$user->isReviewer)) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
    
        $stations = ProviderT::getStationList();
        $order->routes = $order->getRoutes();
    
        $rtn = $this->_getFlags($order->status);
        $rtn['contacterName'] = $order->contactName;
        $rtn['contacterMobile'] = $order->contactMobile;
        $rtn['passengers'] = array_values(UserPassenger::parsePassengers($order->passengers));
        $rtn = array_merge($rtn, F::arrayGetByKeys($order, array('id', 'orderPrice', 'invoicePrice', 'insurePrice', 'reason', 'ctime')));
        $rtn['status'] = TrainStatus::getUserDes($order['status']);
        foreach ($order->routes as $routeType => $route) {
            $tmp = F::arrayGetByKeys($route, array('trainNo', 'departTime', 'arriveTime', 'ticketPrice'));
            $tmp['seatType'] = DictTrain::$seatTypes[$route->seatType]['name'];
            $tmp['departStation'] = $stations[$route->departStationCode]['name'];
            $tmp['arriveStation'] = $stations[$route->arriveStationCode]['name'];
            $tmp['tickets'] = array();
            foreach ($order->tickets as $ticket) {
                $tmpTicket = UserPassenger::parsePassenger($ticket->passenger);
                $tmpTicket = array_merge($tmpTicket, F::arrayGetByKeys($ticket, array('trainNo', 'departStationCode', 'arriveStationCode', 'departTime', 'arriveTime', 'ticketPrice', 'ticketInfo', 'ticketNo')));
                $tmp['seatType'] = DictTrain::$seatTypes[$ticket->seatType]['name'];
                $tmpTicket['isResign'] = $ticket->status == TrainStatus::RSN_SUCC;
                $tmpTicket['isRefund'] = in_array($ticket->status, TrainStatus::getRefundingTicketStatus());
                $tmp['tickets'][] = $tmpTicket;
            }

            $rtn[$routeType] = $tmp;
        }
    
        $this->corAjax($rtn);
    }
    
    public function actionReview() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ, 'status' => ParamsFormat::BOOL)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
    
        if (!($order = TrainOrder::model()->findByPk($params['orderID']))) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
    
        $status = $params['status'] ? TrainStatus::CHECK_SUCC : TrainStatus::CHECK_FAIL;
    
        $this->onAjax($order->changeStatus($status, array('reviewerID' => $user)));
    }
    
    public function actionCancel() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
    
        if (!($order = TrainOrder::model()->findByPk($params['orderID'])) || $order->userID != $user->id) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
    
        $this->onAjax($order->changeStatus(TrainStatus::CANCELED));
    }
}