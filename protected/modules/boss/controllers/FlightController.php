<?php
class FlightController extends BossController {
    public function actionIndex() {
        if (!($params = F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ, 'beginDate' => '!' . ParamsFormat::DATE . '--' . Q_DATE, 'endDate' => '!' . ParamsFormat::DATE . '--' . Q_DATE)))) {
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
}