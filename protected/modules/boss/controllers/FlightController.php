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
    
    public function actionChangeStatus() {
        if (!F::checkParams($_POST, array('orderID' => ParamsFormat::INTNZ, 'status' => ParamsFormat::F_STATUS))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($order = FlightCNOrder::model()->findByPk($_POST['orderID']))) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $_POST['operaterID'] = $this->bossAdmin->id;
        
        $this->onAjax($order->changeStatus($_POST['status'], $_POST));
    }
    
    public function actionGetChangeStatusHtml() {
        if (!($params = F::checkParams($_GET, array('orderID' => ParamsFormat::INTNZ, 'status' => ParamsFormat::F_STATUS)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($order = FlightCNOrder::model()->findByPk($params['orderID']))) {
            $this->errAjax(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $statusStr = FlightStatus::$flightStatus[$params['status']]['str'];
        $func = '_cS2' . $statusStr . 'Html';
        if (!method_exists($this, $func)) {
            $this->errAjax(RC::RC_ERROR);
        }
        
        $this->corAjax(array('html' => $this->$func($order)));
    }
    
    private function _cS2BookSuccHtml($order) {
        $rtn = '';
        $orderInit = FlightCNOrder::initWithSegments($order, True);
        $passengers = FlightCNOrder::classifyPassengers($orderInit['passengers']);
        $routeTypes = $order->isRound ? array('departRoute', 'returnRoute') : array('departRoute');
        $segmentNum = 0;
        foreach ($routeTypes as $routeType) {
            foreach ($orderInit[$routeType]['segments'] as $segment) {
                $segmentPassengerHtml = '';
                $segmentNum ++;
                $marginClass = $segmentNum <= 1 ? ' row-form-margin' : '';
                $rtn .= "<div class='row'><div class='col-sm-12 text-center text-info'>{$segment['departCity']}-{$segment['arriveCity']}</div></div>";
                foreach ($passengers as $ticketType => $subPassengers) {
                    if (count($subPassengers) <= 0) {
                        continue;
                    }
                    $ticketTypeStr = DictFlight::$ticketTypes[$ticketType]['str'];
                    $ticketTypeName = DictFlight::$ticketTypes[$ticketType]['name'];
                    $rtn .= "<div class='row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeName}大PNR</div><div class='col-sm-9 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_{$routeType}[{$segment['id']}][{$ticketTypeStr}BigPNR]' data-format='F_PNR' data-err='{$ticketTypeName}大PNR错误' /></div></div>";
                    $rtn .= "<div class='row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeName}小PNR</div><div class='col-sm-9 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_{$routeType}[{$segment['id']}][{$ticketTypeStr}SmallPNR]' data-format='F_PNR' data-err='{$ticketTypeName}小PNR错误' /></div></div>";
                    $rtn .= "<div class='row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeName}票价</div><div class='col-sm-9 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_{$routeType}[{$segment['id']}][{$ticketTypeStr}TicketPrice]' data-format='FLOATNZ' data-err='{$ticketTypeName}票价错误' /></div></div>";
                    $rtn .= "<div class='row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeName}机建</div><div class='col-sm-9 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_{$routeType}[{$segment['id']}][{$ticketTypeStr}AirportTax]' data-format='FLOAT' data-err='{$ticketTypeName}机建错误' /></div></div>";
                    $rtn .= "<div class='row row-form-margin hidden'><div class='col-sm-3 text-right'>{$ticketTypeName}燃油</div><div class='col-sm-9 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_{$routeType}[{$segment['id']}][{$ticketTypeStr}OilTax]' data-format='FLOAT' data-err='{$ticketTypeName}燃油错误' value='0' /></div></div>";
                    
                    foreach ($subPassengers as $passenger) {
                        $segmentPassengerHtml .= "<div class='row row-form-margin'><div class='col-sm-3 text-right'>{$passenger['name']}票号</div><div class='col-sm-9 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_{$routeType}[{$segment['id']}][ticketNo][{$passenger['id']}]' data-format='F_TICKET_NO' data-err='{$passenger['name']}票号错误' /></div></div>";
                    }
                }
                
                $rtn .= $segmentPassengerHtml;
            } 
        }
        
        return $rtn;
    }
}