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
    
    public function actionOrderList() {
        $defaultBeginDate = date('Y-m-d', strtotime('-1 month'));
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ, 'beginDate' => '!' . ParamsFormat::DATE . '--' . $defaultBeginDate, 'endDate' => '!' . ParamsFormat::DATE . '--' . Q_DATE)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $rtn = array();
        
        $res = FlightCNOrder::search($params);
        foreach ($res['data'] as $order) {
            $tmp = F::arrayGetByKeys($order, array('id', 'orderPrice', 'isRound', 'status', 'ctime'));
            $tmp = array_merge($tmp, F::arrayGetByKeys($order['departRoute'], array('departCity', 'arriveCity', 'departTime')));
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
        if ($user->isReviewer) {
            $res = FlightCNOrder::search(array('departmentID' => $user->departmentID, 'status' => FlightStatus::$flightStatusGroup['waitCheck']));
            foreach ($res['data'] as $order) {
                $tmp = F::arrayGetByKeys($order, array('id', 'contacterName', 'contacterMobile', 'orderPrice', 'isRound', 'ctime'));
                $tmp = array_merge($tmp, F::arrayGetByKeys($order['departRoute'], array('departCity', 'arriveCity', 'departTime')));
                
                $rtn[] = $tmp;
            }
        }
        
        $this->corAjax(array('reviewOrderList' => $rtn));
    }
    
    public function actionOrderDetail() {
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $res = FlightCNOrder::search($_GET, False, True, True);
        if (empty($res['data'])) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        $order = current($res['data']);
        
        $this->corAjax($order);
    }
    
    public function actionReview() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ, 'status' => ParamsFormat::BOOL)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        if (!($order = FlightCNOrder::model()->findByPk($params['orderID'])) || $order->userID != $user->id) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $status = $params['status'] ? FlightStatus::CHECK_SUCC : FlightStatus::CHECK_FAIL;
        
        $this->onAjax($order->changeStatus(FlightStatus::$status));
    }
}