<?php
class FlightController extends BossController {
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
    
    public function actionGetOrderDetailHtml() {
        if (!F::checkParams($_GET, array('orderID' => ParamsFormat::INT)) || !($order = FlightCNOrder::model()->findByPk($_GET['orderID']))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $finances = CompanyFinanceLog::model()->findAllByAttributes(array('orderID' => $order->id), array('order' => 'id DESC'));
        $logs = Log::model()->findAllByAttributes(array('orderID' => $order->id, 'type' => Dict::BUSINESS_FLIGHT), array('order' => 'id DESC'));
        
        $rtn = array('html' => $this->renderPartial('_orderDetail', array('order' => $order, 'finances' => $finances, 'logs' => $logs), True));
        $this->corAjax($rtn);
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
        $passengers = FlightCNOrder::parsePassengers($order->passengers);
        $routeTypes = $order->isRound ? array('departRoute', 'returnRoute') : array('departRoute');
        $segmentNum = 0;
        $cities = ProviderF::getCNCityList();
        foreach ($routeTypes as $routeType) {
            foreach ($routes[$routeType]['segments'] as $segment) {
                $segmentPassengerHtml = '';
                $marginClass = $segmentNum++ >= 1 ? ' row-form-margin' : '';
                $rtn .= "<div class='row{$marginClass}'><div class='col-sm-12 text-center text-info'>{$cities[$segment->departCityCode]['cityName']}-{$cities[$segment->arriveCityCode]['cityName']}</div></div>";
                foreach ($passengers as $passengerID => $passenger) {
                    $ticketTypeStr = DictFlight::$ticketTypes[$passenger['type']]['str'];
                    $ticketTypeName = DictFlight::$ticketTypes[$passenger['type']]['name'];
                    $ticketPrice = $segment[$ticketTypeStr . 'Price'] / 100;
                    $airportTax = $segment[$ticketTypeStr . 'AirportTax'] / 100;
                    $oilTax = $segment[$ticketTypeStr . 'OilTax'] / 100;
                    
                    $rtn .= "<div class='row row-form-margin'><div class='col-sm-2 text-right'>{$passenger['name']}({$ticketTypeName})</div><div class='col-sm-10 form-inline'>";
                    $rtn .= "<div class='form-group form-group-sm'><label>PNR</label> <input type='text' class='form-control' name='cS2BookSucc_segments[{$segment->id}][{$passengerID}][smallPNR]' data-format='F_PNR' data-err='{$passenger['name']}({$ticketTypeName})PNR错误' size='5' /> </div>";
                    $rtn .= "<div class='form-group form-group-sm'><label>票价</label> <input type='text' class='form-control' name='cS2BookSucc_segments[{$segment->id}][{$passengerID}][ticketPrice]' data-format='FLOATNZ' data-err='{$passenger['name']}({$ticketTypeName})票价错误' value='{$ticketPrice}' size='5' /> </div>";
                    $rtn .= "<div class='form-group form-group-sm'><label>实付票价</label> <input type='text' class='form-control' name='cS2BookSucc_segments[{$segment->id}][{$passengerID}][realTicketPrice]' data-format='FLOATNZ' data-err='{$passenger['name']}({$ticketTypeName})实付票价错误' value='{$ticketPrice}' size='5' /> </div>";
                    $rtn .= "<div class='form-group form-group-sm'><label>机建</label> <input type='text' class='form-control' name='cS2BookSucc_segments[{$segment->id}][{$passengerID}][airportTax]' data-format='FLOAT' data-err='{$passenger['name']}({$ticketTypeName})机建错误' value='{$airportTax}' size='2' /> </div>";
                    $rtn .= "<div class='form-group form-group-sm hidden'><label>燃油</label> <input type='text' class='form-control' name='cS2BookSucc_segments[{$segment->id}][{$passengerID}][oilTax]' data-format='FLOAT' data-err='{$passenger['name']}({$ticketTypeName})燃油错误' value='{$oilTax}' size='2' /> </div>";
                    $rtn .= "<div class='form-group form-group-sm'><label>票号</label> <input type='text' class='form-control' name='cS2BookSucc_segments[{$segment->id}][{$passengerID}][ticketNo]' data-format='F_TICKET_NO' data-err='{$passenger['name']}({$ticketTypeName})票号错误' size='14' /> </div>";
                    $rtn .= '</div></div>';
                }
            } 
        }
        
        return $rtn;
    }
    
    private function _cS2RsnAgreeHtml($order) {
        $cities = ProviderF::getCNCityList();
        $routes = $order->getRoutes();
        
        $rtn = '<div class="row"><div class="col-sm-2 text-right">改签乘客</div><div class="col-sm-10">';
        $routeTypes = $order->isRound ? array('departRoute', 'returnRoute') : array('departRoute');
        foreach ($routeTypes as $routeType) {
            foreach ($routes[$routeType]['segments'] as $segment) {
                $segmentHtml = "<div class='text-danger'><label>{$cities[$segment->departCityCode]['cityName']}-{$cities[$segment->arriveCityCode]['cityName']}</label></div>";
                $segmentHtml .= '<div>';
                $ticketNum = 0;
                foreach ($segment->tickets as $ticket) {
                    if (!in_array($ticket->status, FlightStatus::getCanResignTicketStatus())) {
                        continue;
                    }
                    $ticketNum++;
                    $passenger = FlightCNOrder::parsePassenger($ticket->passenger);
                    $ticketType = DictFlight::$ticketTypes[$passenger['type']]['name'];
                    $departTime = date('Y-m-d H:i', $ticket->departTime);
                    $arriveTime = date('Y-m-d H:i', $ticket->arriveTime);
                    $ticketPrice = $ticket->ticketPrice / 100;
                    $airportTax = $ticket->airportTax / 100;
                    $oilTax = $ticket->oilTax / 100;
                    $segmentHtml .= "<label class='checkbox-inline'> <input type='checkbox' class='c_select_ticket' value='{$ticket->id}' name='cS2RsnAgree_ticketIDs[{$ticket->id}]' data-segment-id='{$ticket->segmentID}' data-passenger='{$passenger['name']}({$ticketType})' data-flight-no='{$ticket->flightNo}' data-cabin-class='{$ticket->cabinClass}' data-depart-time='{$departTime}' data-arrive-time='{$arriveTime}' data-is-insured='{$ticket->isInsured}' data-ticket-price='{$ticketPrice}' data-airport-tax='{$airportTax}' data-oil-tax='{$oilTax}' />{$passenger['name']}({$ticketType})</label>";
                }
                $segmentHtml .= '</div>';
                
                if ($ticketNum > 0) {
                    $rtn .= $segmentHtml;
                }
            }
        }
        $rtn .= '</div></div>';
        $rtn .= "<div class='row row-form-margin'><div class='col-sm-2 text-right'>改签信息</div><div class='col-sm-10 form-inline'>";
        $rtn .= '<div class="form-group form-group-sm"><label>航班号</label> <input type="text" name="cS2RsnAgree_flightNo" class="form-control" data-format="F_FLIGHT_NO" data-err="航班号错误" size="6" /> </div>';
        $rtn .= '<div class="form-group form-group-sm"><label>出发时间</label> <input type="text" name="cS2RsnAgree_departTime" class="c_time form-control" data-flag="beginTime" data-format="DATE_HM" data-err="出发时间错误" readonly size="16" /> </div>';
        $rtn .= '<div class="form-group form-group-sm"><label>到达时间</label> <input type="text" name="cS2RsnAgree_arriveTime" class="c_time form-control" data-flag="endTime" id="datePickerEnd" data-format="DATE_HM" data-err="到达时间错误" readonly size="16" /> </div>';
        $rtn .= '<div class="form-group form-group-sm"><label>舱位类别</label> <select name="cS2RsnAgree_cabinClass" class="form-control" data-format="INTNZ" data-err="请选择舱位类别"><option value="0">----请选择----';
        foreach (DictFlight::$cabinClasses as $cabinClass => $cabinConfig) {
            $rtn .= "<option value='{$cabinClass}'>{$cabinConfig['name']}";
        }
        $rtn .= '</select> </div>';
        $rtn .= '<div class="form-group form-group-sm"><label class="checkbox-inline"> <input type="checkbox" name="cS2RsnAgree_isInsured" value="1" /><b class="text-danger">购买保险</b></label> </div>';
        $rtn .= '</div></div>';
        
        return $rtn;
    }
    
    private function _cS2RsnSuccHtml($order) {
        $rtn = '';
        $cities = ProviderF::getCNCityList();
        $ticketNum = 0;
        foreach ($order->tickets as $ticket) {
            if ($ticket->status != FlightStatus::RSN_AGREE) {
                continue;
            }
            
            if ($ticketNum++ <= 0) {
                $segments = F::arrayAddField($order->segments, 'id');
                $rtn .= "<div class='row'><div class='col-sm-12 text-danger text-center'>{$cities[$segments[$ticket->segmentID]['departCityCode']]['cityName']}-{$cities[$segments[$ticket->segmentID]['arriveCityCode']]['cityName']}</div></div>";
            }
        
            $passenger = FlightCNOrder::parsePassenger($ticket->passenger);
            $ticketType = DictFlight::$ticketTypes[$passenger['type']]['name'];
            $passengerName = "{$passenger['name']}({$ticketType})";
            $realTicketPrice = $ticket->ticketPrice / 100;
            $realResignHandlePrice = $ticket->resignHandlePrice / 100;
            
            $rtn .= "<div class='row row-form-margin'><div class='col-sm-2 text-right'>{$passengerName}</div><div class='col-sm-10 form-inline'>";
            $rtn .= "<div class='form-group form-group-sm'><label>PNR</label> <input type='text' name='cS2RsnSucc_tickets[{$ticket->id}][smallPNR]' class='form-control' data-format='F_PNR' data-err='{$passengerName}PNR错误' value='{$ticket->smallPNR}' size='6' /> </div>";
            $rtn .= "<div class='form-group form-group-sm'><label>实际票价</label> <input type='text' name='cS2RsnSucc_tickets[{$ticket->id}][realTicketPrice]' class='form-control' data-format='FLOATNZ' data-err='{$passengerName}实际票价错误' value='{$realTicketPrice}' size='6' /> </div>";
            $rtn .= "<div class='form-group form-group-sm'><label>实际手续费</label> <input type='text' name='cS2RsnSucc_tickets[{$ticket->id}][realResignHandlePrice]' class='form-control' data-format='FLOAT' data-err='{$passengerName}实际手续费错误' value='{$realResignHandlePrice}' size='6' /> </div>";
            $rtn .= "<div class='form-group form-group-sm'><label>票号</label> <input type='text' name='cS2RsnSucc_tickets[{$ticket->id}][ticketNo]' class='form-control' data-format='F_TICKET_NO' data-err='{$passengerName}票号错误' value='{$ticket->ticketNo}' size='15' /> </div>";
            $rtn .= '</div></div>';
        }
        
        return $rtn;
    }
    
    private function _cS2RfdAgreeHtml($order) {
        $rtn = '';
        $cities = ProviderF::getCNCityList();
        $routes = $order->getRoutes();
        $routeTypes = $order->isRound ? array('departRoute', 'returnRoute') : array('departRoute');
        foreach ($routeTypes as $routeType) {
            foreach ($routes[$routeType]['segments'] as $segment) {
                $ticketNum = 0;
                $segmentHtml = "<div class='row row-form-margin'><div class='col-sm-12 text-center text-danger'>{$cities[$segment->departCityCode]['cityName']}-{$cities[$segment->arriveCityCode]['cityName']}</div></div>";
                $segmentHtml .= "<div class='row row-form-margin'><div class='col-sm-4 text-right'>选择乘客</div><div class='col-sm-6'>";
                foreach ($order->tickets as $ticket) {
                    if ($ticket->segmentID != $segment->id || !in_array($ticket->status, FlightStatus::getCanRefundTicketStatus())) {
                        continue;
                    }
                    
                    $ticketNum++;
                    $passenger = FlightCNOrder::parsePassenger($ticket->passenger);
                    $ticketTypeName = DictFlight::$ticketTypes[$passenger['type']]['name'];
                    $passengerName = "{$passenger['name']}($ticketTypeName)";
                    
                    $segmentHtml .= '<div class="checkbox form-inline">';
                    $segmentHtml .= "<label><input type='checkbox' class='c_select_ticket' data-ticket-id='{$ticket->id}' data-passenger-name='{$passengerName}' data-ticket-price='{$ticket->realTicketPrice}' data-airport-tax='{$ticket->airportTax}' data-oil-tax='{$ticket->oilTax}' />{$passengerName}</label>";
                    $segmentHtml .= '</div>';
                }
                $segmentHtml .= '</div></div>';
                
                $rtn .= $ticketNum > 0 ? $segmentHtml : '';
            }
        }
        
        return $rtn;
    }
    
    private function _cS2RfdedHtml($order) {
        $rtn = '<div class="row"><div class="col-sm-4 text-right">退款乘客</div><div class="col-sm-6">';
        $cities = ProviderF::getCNCityList();
        $segments = F::arrayAddField($order->segments, 'id');
        $classifyTickets = FlightCNOrder::classifyTickets($order->tickets);
        $tickets = empty($classifyTickets[FlightStatus::RFD_AGREE]) ? array() : $classifyTickets[FlightStatus::RFD_AGREE];
        $ticketNum = 0;
        foreach ($tickets as $ticket) {
            $passenger = FlightCNOrder::parsePassenger($ticket->passenger);
            $ticketTypeName = DictFlight::$ticketTypes[$passenger['type']]['name'];
            $passengerName = "{$passenger['name']}($ticketTypeName)";
            $refundPrice = ($ticket->ticketPrice + $ticket->airportTax + $ticket->oilTax - $ticket->refundHandlePrice) / 100;
            
            $rtn .= "<div class='checkbox form-inline'><label><input type='checkbox' class='c_select_ticket' data-ticket-id='{$ticket->id}' data-passenger-name='{$passengerName}' data-refund-price='{$refundPrice}' />{$passengerName}</label></div>";
        }
        $rtn .= '</div></div>';
        
        return $rtn;
    }
}