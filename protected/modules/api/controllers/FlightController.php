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
        /*
        if (Q::isLocalEnv()) {
            $_POST = self::_getOrderParams();
        }
        */
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
        $criteria = new CDbCriteria();
        $criteria->compare('userID', $params['userID']);
        $criteria->addBetweenCondition('ctime', strtotime($params['beginDate']), strtotime($params['endDate']));

        $rtn = array();
        
        $keys = array(
            'id', 'departAirportCode', 'arriveAirportCode', 'departCity', 'arriveCity', 'departTime', 'arriveTime', 'ctime',
            'orderPrice', 'insurePrice', 'invoicePrice', 'airlineCode', 'craftCode', 'craftType'
        );
        
        $cities = DataAirport::getCNCities();
        $airports = DataAirport::getCNAiports();
        
        $orders = FlightCNOrder::model()->findAllByAttributes(array('userID' => $_GET['userID']));
        foreach ($orders as $order) {
            $index = empty($order->batchNo) ? $order->id : $order->batchNo;
            $routeType = $order->isBack ? 'returnRoute' : 'departRoute';
            if (empty($rtn[$index])) {
                $rtn[$index] = array('orderPrice' => 0, 'insurePrice' => 0, 'invoicePrice' => 0);
            }
            if (empty($rtn[$index][$routeType]['segments'])) {
                $rtn[$index][$routeType]['segments'] = array();
            }
            
            $rtn[$index]['orderPrice'] += $order->orderPrice;
            $rtn[$index]['insurePrice'] += $order->insurePrice;
            $rtn[$index]['invoicePrice'] += $order->invoicePrice;
            
            $tmp = F::arrayGetByKeys($order, $keys);
            $tmp['departAirport'] = $airports[$order['departAirportCode']]['airportName'];
            $tmp['arriveAirport'] = $airports[$order['arriveAirportCode']]['airportName'];
            $tmp['departCity'] = $cities[$order['departCityCode']]['cityName'];
            $tmp['arriveCity'] = $cities[$order['arriveCityCode']]['cityName'];
            
            $rtn[$index][$routeType]['segments'][] = $tmp;
        }
        
        $this->corAjax(array_values($rtn));
    }
    
    public function actionOrderDetail() {
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ, 'orderID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($order = FlightCNOrder::model()->findByPk($params['orderID'])) || $order->userID != $params['userID']) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $orders = array($order);
        if (!empty($order->batchNo)) {
            $orders = FlightCNOrder::model()->findAllByAttributes(array('batchNo' => $order->batchNo));
        }
        
        $keys = array(
            'departAirportCode', 'arriveAirportCode', 'departCity', 'arriveCity', 'departTime', 'arriveTime', 'ctime',
            'orderPrice', 'insurePrice', 'invoicePrice', 'airlineCode', 'craftCode', 'craftType'
        );
        
        $cities = DataAirport::getCNCities();
        $airports = DataAirport::getCNAiports();
        
        $rtn = array('orderPrice' => 0, 'insurePrice' => 0, 'invoicePrice' => 0, 'passengers' => array(), 'id' => $order->id);
        foreach ($orders as $index => $order) {
            if (empty($rtn['passengers'])) {
                $passengerIDs = explode('-', $order->passengerIDs);
                $criteria = new CDbCriteria();
                $criteria->addInCondition('id', $passengerIDs);
                $passengers = UserPassenger::model()->findAll($criteria);
                foreach ($passengers as $passenger) {
                    $rtn['passengers'][] = F::arrayGetByKeys($passenger, array('name', 'type', 'cardType', 'cardNo', 'birthday', 'sex'));
                }
            }
            
            $routeType = $order->isBack ? 'returnRoute' : 'departRoute';
            if (empty($rtn[$routeType]['segments'])) {
                $rtn[$routeType]['segments'] = array();
            }
            
            $rtn['orderPrice'] += $order->orderPrice;
            $rtn['insurePrice'] += $order->insurePrice;
            $rtn['invoicePrice'] += $order->invoicePrice;
            
            $tmp = F::arrayGetByKeys($order, $keys);
            $tmp['departAirport'] = $airports[$order['departAirportCode']]['airportName'];
            $tmp['arriveAirport'] = $airports[$order['arriveAirportCode']]['airportName'];
            $tmp['departCity'] = $cities[$order['departCityCode']]['cityName'];
            $tmp['arriveCity'] = $cities[$order['arriveCityCode']]['cityName'];
            
            $rtn[$routeType]['segments'][] = $tmp;
        }
        
        $this->corAjax($rtn);
    }
}