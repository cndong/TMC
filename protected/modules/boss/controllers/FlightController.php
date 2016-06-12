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
        
        $_POST['operaterID'] = $this->admin->id;
        
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
        $routes = $order->getRoutes();
        $classifyPassengers = FlightCNOrder::classifyPassengers(FlightCNOrder::parsePassengers($order->passengers));
        $routeTypes = $order->isRound ? array('departRoute', 'returnRoute') : array('departRoute');
        $segmentNum = 0;
        $cities = ProviderF::getCNCityList();
        foreach ($routeTypes as $routeType) {
            foreach ($routes[$routeType]['segments'] as $segment) {
                $segmentPassengerHtml = '';
                $marginClass = $segmentNum++ >= 1 ? ' row-form-margin' : '';
                $rtn .= "<div class='row{$marginClass}'><div class='col-sm-12 text-center text-info'>{$cities[$segment->departCityCode]['cityName']}-{$cities[$segment->arriveCityCode]['cityName']}</div></div>";
                foreach ($classifyPassengers as $ticketType => $passengers) {
                    if (count($passengers) <= 0) {
                        continue;
                    }
                    $ticketTypeStr = DictFlight::$ticketTypes[$ticketType]['str'];
                    $ticketTypeName = DictFlight::$ticketTypes[$ticketType]['name'];
                    $rtn .= "<div class='row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeName}PNR</div><div class='col-sm-6 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_segments[{$segment->id}][{$ticketTypeStr}SmallPNR]' data-format='F_PNR' data-err='{$ticketTypeName}PNR错误' /></div></div>";
                    $rtn .= "<div class='row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeName}票价</div><div class='col-sm-6 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_segments[{$segment->id}][{$ticketTypeStr}TicketPrice]' data-format='FLOATNZ' data-err='{$ticketTypeName}票价错误' /></div></div>";
                    $rtn .= "<div class='row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeName}机建</div><div class='col-sm-6 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_segments[{$segment->id}][{$ticketTypeStr}AirportTax]' data-format='FLOAT' data-err='{$ticketTypeName}机建错误' /></div></div>";
                    $rtn .= "<div class='row row-form-margin hidden'><div class='col-sm-3 text-right'>{$ticketTypeName}燃油</div><div class='col-sm-6 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_segments[{$segment->id}][{$ticketTypeStr}OilTax]' data-format='FLOAT' data-err='{$ticketTypeName}燃油错误' value='0' /></div></div>";
                    
                    foreach ($passengers as $passengerKey => $passenger) {
                        $segmentPassengerHtml .= "<div class='row row-form-margin'><div class='col-sm-3 text-right'>{$passenger['name']}票号</div><div class='col-sm-6 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_segments[{$segment->id}][ticketNo][{$passengerKey}]' data-format='F_TICKET_NO' data-err='{$passenger['name']}票号错误' /></div></div>";
                    }
                }
                
                $rtn .= $segmentPassengerHtml;
            } 
        }
        
        return $rtn;
    }
    
    private function _cS2RsnAgreeHtml($order) {
        $rtn = '<div class="row"><div class="col-sm-3 text-right">改签乘客</div><div class="col-sm-9">';
        
        $initOrder = $order->getTicketsWithRouteType();
        $passengers = $order->getPassengerObjs();
        $cities = ProviderF::getCNCityList();
        $routeTypes = $order->isRound ? array('departRoute', 'returnRoute') : array('departRoute');
        foreach ($routeTypes as $routeType) {
            foreach ($initOrder[$routeType]['segments'] as $segment) {
                $rtn .= "<div class='text-danger'><label>{$cities[$segment->departCityCode]['cityName']}-{$cities[$segment->arriveCityCode]['cityName']}</label></div>";
                $rtn .= '<div>';
                foreach ($segment->tickets as $ticket) {
                    $ticketType = DictFlight::$ticketTypes[$passengers[$ticket->passengerID]['type']]['name'];
                    $rtn .= "<label class='checkbox-inline'><input type='checkbox' class='c_select_ticket' value='{$ticket->id}' name='cS2RsnAgree_ticketIDs[]' data-ticket-type='{$passengers[$ticket->passengerID]['type']}' />{$passengers[$ticket->passengerID]->name}({$ticketType})</label>";
                }
                $rtn .= '</div>';
            }
        }
        $rtn .= '</div></div>';
        $rtn .= '<div class="row row-form-margin"><div class="col-sm-3 text-right">航班号</div><div class="col-sm-6"><input type="text" name="cS2RsnAgree_flightNo" class="form-control input-sm" data-format="F_FLIGHT_NO" data-err="航班号错误" /></div></div>';
        $rtn .= '<div class="row row-form-margin"><div class="col-sm-3 text-right">舱位</div><div class="col-sm-6"><input type="text" name="cS2RsnAgree_cabin" class="form-control input-sm" data-format="F_CABIN" data-err="舱位错误" /></div></div>';
        $rtn .= '<div class="row row-form-margin"><div class="col-sm-3 text-right">舱位名称</div><div class="col-sm-6"><input type="text" name="cS2RsnAgree_cabinClassName" class="form-control input-sm" data-format="TEXTNZ" data-err="舱位名称错误" /></div></div>';
        $rtn .= '<div class="row row-form-margin"><div class="col-sm-3 text-right">舱位类别</div><div class="col-sm-6"><select name="cS2RsnAgree_cabinClass" class="form-control input-sm" data-format="INTNZ" data-err="请选择舱位类别"><option value="0">----请选择----';
        foreach (DictFlight::$cabinClasses as $cabinClass => $cabinConfig) {
            $rtn .= "<option value='{$cabinClass}'>{$cabinConfig['name']}";
        }
        $rtn .= '</select></div></div>';
        $rtn .= '<div class="row row-form-margin"><div class="col-sm-3 text-right">机型代码</div><div class="col-sm-6"><input type="text" name="cS2RsnAgree_craftCode" class="form-control input-sm" data-format="F_CRAFT_CODE" data-err="机型代码错误" /></div></div>';
        $rtn .= '<div class="row row-form-margin"><div class="col-sm-3 text-right">机型类别</div><div class="col-sm-6"><select name="cS2RsnAgree_craftType" class="form-control input-sm" data-format="INTNZ" data-err="机型类别错误"><option value="0">----请选择----';
        foreach (DictFlight::$craftTypes as $craftType => $craftConfig) {
            $rtn .= "<option value='{$craftType}'>{$craftConfig['name']}";
        }
        $rtn .= '</select></div></div>';
        $rtn .= '<div class="row row-form-margin"><div class="col-sm-3 text-right">购买保险</div><div class="col-sm-6"><label class="radio-inline"><input type="radio" name="cS2RsnAgree_isInsured" value="1" checked />购买保险</label><label class="radio-inline"><input type="radio" name="cS2RsnAgree_isInsured" value="0" />不买保险</label></div></div>';
        foreach (DictFlight::$ticketTypes as $ticketType => $ticketTypeConfig) {
            $ticketTypeStr = $ticketTypeConfig['str'];
            $rtn .= "<div class='t_ticketTypes row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeConfig['name']}票价</div><div class='col-sm-6 text-left'><input type='text' class='form-control input-sm' name='cS2BookSucc_segments[{$segment->id}][{$ticketTypeStr}TicketPrice]' data-format='FLOATNZ' data-err='{$ticketTypeName}票价错误' /></div></div>";
            $rtn .= "<div class='t_ticketTypes row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeConfig['name']}机建</div></div>";
            $rtn .= "<div class='t_ticketTypes row row-form-margin'><div class='col-sm-3 text-right'>{$ticketTypeConfig['name']}燃油</div></div>";
        }
        
        
        return $rtn;
    }
}