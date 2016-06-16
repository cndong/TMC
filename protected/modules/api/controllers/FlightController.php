<?php
class FlightController extends ApiController {
    public function actionCityList() {
        $rtn = array('cityList' => array(), 'hotList' => array());
        $cityList = ProviderF::getCNCityList();
        foreach ($cityList as &$city) {
            $city['firstChar'] = $firstChar = strtoupper($city['citySpell']{0});
            if (!isset($rtn['cityList'][$firstChar])) {
                $rtn['cityList'][$firstChar] = array(
                    'cities' => array(),
                    'firstChar' => $firstChar
                );
            }
            
            $rtn['cityList'][$firstChar]['cities'][] = $city;
        }
        
        $rtn['cityList'] = array_values($rtn['cityList']);
        $rtn['hotList'] = array_values(F::arrayGetByKeys($cityList, array('BJS', 'SHA', 'CAN')));
        
        $this->corAjax($rtn);
    }
    
    public function actionFlightList() {
        $res = ProviderF::getCNFlightList($_GET);
        $rtn = F::isCorrect($res) ? $res['data'] : array();
        
        $this->corAjax($rtn);
    }
    
    public function actionFlightDetail() {
        $this->onAjax(ProviderF::getCNFlightDetail($_GET));
    }
    
    private static function _getOrderParams() {
        return array(
            'merchantID' => 1,
            'userID' => 1,
            'isPrivate' => 0,
            'isInsured' => 0,
            'isInvoice' => 0,
            'isRound' => 0,
            'contacter' => '{
                "name": "随永杰",
                "mobile": "13141353663"
            }',
            'passengers' => '[
                {
                    "name": "随永杰",
                    "type": 1,
                    "cardType": 1,
                    "cardNo": "135596199911215888",
                    "birthday": "1999-12-21",
                    "sex": 0
                }
            ]',
            'price' => '{
                "orderPrice": 67000,
                "ticketPrice": 62000,
                "airportTaxPrice": 5000,
                "oilTaxPrice": 0,
                "insurePrice": 0,
                "invoicePrice": 0
            }',
            'departRoute' => '{
                "departCityCode": "BJS",
                "arriveCityCode": "SHA",
                "departDate": "2016-06-22",
                "routeKey": "0415319315b6c684b3e0c3c097bd7b49",
                "segments": [
                    {
                        "flightNo": "CZ9271",
                        "departCityCode": "BJS",
                        "arriveCityCode": "SHA",
                        "departAirportCode": "PEK",
                        "arriveAirportCode": "SHA",
                        "departTime": 1466550000,
                        "arriveTime": 1466558100,
                        "airlineCode": "CZ",
                        "craftCode": "333",
                        "cabinInfo": {
                            "cabin": "E",
                            "cabinClass": 3,
                            "adultPrice": 62000,
                            "childPrice": 62000,
                            "babyPrice": 12400
                        },
                        "adultAirportTax": 5000,
                        "adultOilTax": 0,
                        "childAirportTax": 0,
                        "childOilTax": 0,
                        "babyAirportTax": 0,
                        "babyOilTax": 0
                    }
                ]
            }'
        );
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
        
        if (!F::isCorrect($res = FlightCNOrder::createOrder($_POST))) {
            $this->onAjax($res);
        }
        
        $this->corAjax(array('orderID' => $res['data']->id));
    }
    
    private function _getFlags($status) {
        return array(
            'isReview' => $status == FlightStatus::WAIT_CHECK,
            'isCancel' => in_array($status, array(FlightStatus::WAIT_CHECK, FlightStatus::CHECK_SUCC, FlightStatus::WAIT_PAY)),
            'isResign' => $status == FlightStatus::BOOK_SUCC,
            'isPay' => $status == FlightStatus::WAIT_PAY,
            'isRefund' => in_array($status, array(FlightStatus::BOOK_SUCC, FlightStatus::RSN_SUCC))
        );
    }
    
    public function actionOrderList() {
        $defaultBeginDate = date('Y-m-d', strtotime('-1 month'));
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ, 'beginDate' => '!' . ParamsFormat::DATE . '--' . $defaultBeginDate, 'endDate' => '!' . ParamsFormat::DATE . '--' . Q_DATE)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $rtn = array();

        $cities = ProviderF::getCNCityList();
        $res = FlightCNOrder::search($params);
        foreach ($res['data'] as $order) {
            $tmp = F::arrayGetByKeys($order, array('id', 'orderPrice', 'isRound', 'ctime'));
            $tmp['departCity'] = $cities[$order->routes['departRoute']['departCityCode']]['cityName'];
            $tmp['arriveCity'] = $cities[$order->routes['departRoute']['arriveCityCode']]['cityName'];
            $tmp['departTime'] = $order->routes['departRoute']['departTime'];
            $tmp['status'] = FlightStatus::getUserDes($order['status']);
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
        $cities = ProviderF::getCNCityList();
        if ($user->isReviewer) {
            $res = FlightCNOrder::search(array('departmentID' => $user->departmentID, 'status' => FlightStatus::$flightStatusGroup['waitCheck']));
            foreach ($res['data'] as $order) {
                $tmp = F::arrayGetByKeys($order, array('id', 'orderPrice', 'isRound', 'ctime'));
                $tmp['departCity'] = $cities[$order->routes['departRoute']['departCityCode']]['cityName'];
                $tmp['arriveCity'] = $cities[$order->routes['departRoute']['arriveCityCode']]['cityName'];
                $tmp['departTime'] = $order->routes['departRoute']['departTime'];
                $tmp['status'] = FlightStatus::getUserDes($order['status']);
                $rtn[] = $tmp;
            }
        }
        
        $this->corAjax(array('reviewOrderList' => $rtn));
    }
    
    public function actionOrderDetail() {
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $res = FlightCNOrder::search($_GET, False);
        if (empty($res['data'])) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $order = current($res['data']);
        $cities = ProviderF::getCNCityList(); 
        $airports = ProviderF::getCNAirportList();
        $airlines = ProviderF::getAirlineList();
        
        $rtn = $this->_getFlags($order->status);
        $rtn['contacterName'] = $order->contactName;
        $rtn['contacterMobile'] = $order->contactMobile;
        $rtn['passengers'] = array_values(FlightCNOrder::parsePassengers($order->passengers));
        $rtn = array_merge($rtn, F::arrayGetByKeys($order, array('id', 'orderPrice', 'reason', 'ctime')));
        $rtn['status'] = FlightStatus::getUserDes($order['status']);
        foreach (array('departRoute', 'returnRoute') as $routeType) {
            if (empty($order->routes[$routeType])) {
                continue;
            }
            $rtn[$routeType] = $order->routes[$routeType];
            $rtn[$routeType]['departCity'] = $cities[$rtn[$routeType]['departCityCode']]['cityName'];
            $rtn[$routeType]['arriveCity'] = $cities[$rtn[$routeType]['arriveCityCode']]['cityName'];
            foreach ($rtn[$routeType]['segments'] as $segmentID => $segment) {
                $tmp = array(
                    'departTerm' => $segment->departTerm == '--' ? '' : $segment->departTerm,
                    'arriveTerm' => $segment->arriveTerm == '--' ? '' : $segment->arriveTerm,
                    'departCity' => $cities[$segment->departCityCode]['cityName'],
                    'arriveCity' => $cities[$segment->arriveCityCode]['cityName'],
                    'departAirport' => $airports[$segment->departAirportCode]['airportName'],
                    'arriveAirport' => $airports[$segment->arriveAirportCode]['airportName'],
                    'airline' => $airlines[$segment->airlineCode]['name'],
                    'tickets' => array()
                );
                $tmp = array_merge($tmp, F::arrayGetByKeys($segment, array('departTime', 'arriveTime', 'flightNo', 'cabinClassName')));
                
                foreach ($segment->tickets as $ticket) {
                    $tmpTicket = FlightCNOrder::parsePassenger($ticket->passenger);
                    $tmpTicket = array_merge($tmpTicket, F::arrayGetByKeys($ticket, array('ticketNo', 'departTime', 'arriveTime', 'flightNo', 'cabinClassName')));
                    $tmpTicket['departTerm'] = $ticket->departTerm == '--' ? '' : $ticket->departTerm;
                    $tmpTicket['arriveTerm'] = $ticket->arriveTerm == '--' ? '' : $ticket->arriveTerm;
                    $tmpTicket['duration'] = $ticket->arriveTime - $ticket->departTime;
                    $tmpTicket['isResignTicket'] = $ticket->status == FlightStatus::RSN_SUCC;
                    $tmpTicket['isRefundTicket'] = in_array($ticket->status, FlightStatus::getRefundingTicketStatus());
                    $tmp['tickets'][] = $tmpTicket;
                }
                
                $rtn[$routeType]['segments'][$segmentID] = $tmp;
            }
            
            $rtn[$routeType]['segments'] = array_values($rtn[$routeType]['segments']);
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
        
        if (!($order = FlightCNOrder::model()->findByPk($params['orderID']))) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $status = $params['status'] ? FlightStatus::CHECK_SUCC : FlightStatus::CHECK_FAIL;
        
        $this->onAjax($order->changeStatus($status, array('reviewerID' => $user)));
    }
    
    public function actionCancel() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        if (!($order = FlightCNOrder::model()->findByPk($params['orderID'])) || $order->userID != $user->id) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $this->onAjax($order->changeStatus(FlightStatus::CANCELED));
    }
    
    public function actionResign() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        if (!($order = FlightCNOrder::model()->findByPk($params['orderID'])) || $order->userID != $user->id) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $this->onAjax($order->changeStatus(FlightStatus::APPLY_RSN));
    }
    
    public function actionRefund() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        if (!($order = FlightCNOrder::model()->findByPk($params['orderID'])) || $order->userID != $user->id) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $this->onAjax($order->changeStatus(FlightStatus::APPLY_RFD));
    }
}