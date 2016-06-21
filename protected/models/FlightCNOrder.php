<?php
class FlightCNOrder extends QActiveRecord {
    private $_collectParams = array();
    public $routes = array();
    
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return '{{flightCNOrder}}';
    }

    public function rules() {
        return array(
            array('merchantID, userID, departmentID, companyID, contactName, contactMobile, isPrivate, isInsured, isInvoice, isRound, segmentNum, passengers, passengerNum, orderPrice, ticketPrice, airportTaxPrice, oilTaxPrice', 'required'),
            array('merchantID, userID, departmentID, companyID, reviewerID, isPrivate, isInsured, isInvoice, isRound, segmentNum, passengerNum, invoicePrice, invoiceAddress, invoicePostID, operaterID, status, ctime, utime', 'numerical', 'integerOnly' => True),
            array('orderPrice, payPrice, ticketPrice, airportTaxPrice, oilTaxPrice, insurePrice, invoicePostPrice', 'numerical'),
            array('passengers', 'length', 'max' => 655),
            array('invoiceAddress', 'length', 'max' => 500),
            array('invoiceTradeNo, tradeNo', 'length', 'max' => 32),
            array('reason', 'length', 'max' => 500),
            array('id, merchantID, userID, departmentID, companyID, contactName, contactMobile, reviewerID, isPrivate, isInsured, isInvoice, isRound, segmentNum, passengers, passengerNum, orderPrice, payPrice, ticketPrice, airportTaxPrice, oilTaxPrice, insurePrice, invoicePrice, invoicePostPrice, invoicePostID, invoiceTradeNo, tradeNo, reason, operaterID, status, ctime, utime', 'safe', 'on'=>'search'),
        );
    }
    
    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'userID'),
            'department' => array(self::BELONGS_TO, 'Department', 'departmentID'),
            'company' => array(self::BELONGS_TO, 'Company', 'companyID'),
            'segments' => array(self::HAS_MANY, 'FlightCNSegment', 'orderID'),
            'tickets' => array(self::HAS_MANY, 'FlightCNTicket', 'orderID'),
            'reviewer' => array(self::BELONGS_TO, 'User', 'reviewerID'),
            'operater' => array(self::BELONGS_TO, 'BossAdmin', 'operaterID')
        );
    }
    
    public function isPrivate() {
        return $this->isPrivate;
    }
    
    public function getLastDepartTime() {
        $rtn = 0;
        foreach ($this->tickets as $ticket) {
            if ($ticket->departTime > $rtn) {
                $rtn = $ticket->departTime;
            }
        }
        
        return $rtn;
    }
    
    public function isCanApplyResign() {
        return $this->getLastDepartTime() - Q_TIME > DictFlight::RESIGN_BEFORE_TIME;
    }
    
    public function isCanApplyRefund() {
        return $this->getLastDepartTime() - Q_TIME > DictFlight::REFUND_BEFORE_TIME;
    }
    
    public function isCanRefunded() {
        foreach ($this->tickets as $ticket) {
            if (in_array($ticket->status, FlightStatus::getCanRefundedTicketStatus())) {
                return True;
            }
        }
        
        return False;
    }
    
    public static function concatPassenger($passenger) {
        $fields = array('name' , 'type', 'cardType', 'cardNo', 'birthday', 'sex', 'id');
        $attributes = F::arrayGetByKeys($passenger, $fields);
        
        return implode(',', $attributes);
    }
    
    public static function concatPassengers($passengers) {
        $rtn = array();
        foreach ($passengers as $passenger) {
            $rtn[] = self::concatPassenger($passenger);
        }
        
        return implode('|', $rtn);
    }
    
    public static function parsePassenger($passenger) {
        $fields = array('name' , 'type', 'cardType', 'cardNo', 'birthday', 'sex', 'id');
        $passenger = explode(',', $passenger);
        
        return array_combine($fields, $passenger);
    }
    
    public static function parsePassengers($passengers) {
        $rtn = array();
        
        $passengers = explode('|', $passengers);
        foreach ($passengers as $passenger) {
            $passenger = self::parsePassenger($passenger);
            $rtn[$passenger['id']] = $passenger;
        }
        
        return $rtn;
    }
    
    public static function classifyPassengers($passengers) {
        $rtn = array_fill_keys(array_keys(DictFlight::$ticketTypes), array());
        $keys = array('name' , 'type', 'cardType', 'cardNo', 'birthday', 'sex');
        $tmp = False;
        foreach ($passengers as $passenger) {
            if (!$tmp && ((is_object($passenger) && isset($passenger->id)) || (is_array($passenger) && isset($passenger['id'])))) {
                $keys[] = 'id';
                $tmp = True;
            }
            $rtn[$passenger['type']][UserPassenger::getPassengerKey($passenger)] = F::arrayGetByKeys($passenger, $keys);
        }
    
        return $rtn;
    }
    
    public static function classifyTickets($tickets, $field = 'status') {
        $rtn = array();
        foreach ($tickets as $ticket) {
            if (empty($rtn[$ticket->$field])) {
                $rtn[$ticket->$field] = array();
            }
            
            $rtn[$ticket->$field][$ticket->id] = $ticket;
        }
        
        return $rtn;
    }
    
    private static function _getCreateOrderFormats() {
        return array(
            'merchantID' => ParamsFormat::M_ID,
            'userID' => ParamsFormat::INTNZ,
            'isPrivate' => ParamsFormat::BOOL,//因公、因私
            'isInsured' => ParamsFormat::BOOL,//是否购买保险
            'isInvoice' => ParamsFormat::BOOL,//是否邮寄发票
            'isRound' => ParamsFormat::BOOL,//单程、往返
            'contacter' => ParamsFormat::ISARRAY,//需要判断是否是本人的联系人
            'departRoute' => ParamsFormat::ISARRAY,
            'returnRoute' => '!' . ParamsFormat::ISARRAY . '--',//如果是往返票必须有此属性
            'passengers' => ParamsFormat::ISARRAY,
            'price' => ParamsFormat::ISARRAY,
            'invoiceAddress' => '!' . ParamsFormat::ISARRAY . '--',
            'reason' => '!' . ParamsFormat::TEXTNZ . '--'
        );
    }
    
    private static function _checkCreateOrderParams($params) {
        if (!$params = F::checkParams($params, self::_getCreateOrderFormats())) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }

        //检测用户
        if (!($user = User::model()->findByPk($params['userID']))) {
            return F::errReturn(RC::RC_USER_NOT_EXISTS);
        }
        $params['departmentID'] = $params['isPrivate'] ? 0 : $user->departmentID;
        $params['companyID'] = $params['isPrivate'] ? 0 : $user->companyID;
        if (!$params['isPrivate']) {
            if (empty($params['reason'])) {
                return F::errReturn(RC::RC_REASON_ERROR);
            }
        } else {
            $params['reason'] = '';
        }
    
        //检测联系人
        $isUseID = isset($params['contacter']['contacterID']);
        $formats = $isUseID ? array('contacterID' => ParamsFormat::INTNZ) : UserContacter::getCreateOrModifyFormats(True);
        if (!$isUseID) {
            $params['contacter']['userID'] = $params['userID'];
        }
        if (!($tmp = F::checkParams($params['contacter'], $formats))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        if ($isUseID) {
            if (!($contacter = UserContacter::model()->findByPk($tmp['contacterID'])) || $contacter->deleted || $contacter->userID != $params['userID']) {
                return F::errReturn(RC::RC_CONTACTER_NOT_EXISTS);
            }
            $params['contacterObj'] = $contacter;
        } else {
            if ($contacter = UserContacter::model()->findByAttributes($tmp, 'deleted=:deleted', array(':deleted' => UserContacter::DELETED_F))) {
                $params['contacterObj'] = $contacter;
                $tmp = array('contacterID' => $contacter->id);
            }
        }
        
        $params['contacter'] = $tmp;
    
        //检测地址
        if ($params['isInvoice']) {
            if (empty($params['invoiceAddress']) || !is_array($params['invoiceAddress'])) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            $isUseID = isset($params['invoiceAddress']['addressID']);
            if (!$isUseID) {
                $params['invoiceAddress']['userID'] = $params['userID'];
            }
            $formats = $isUseID ? array('addressID' => ParamsFormat::INTNZ) : UserAddress::getCreateOrModifyFormats(True);
            if (!($tmp = F::checkParams($params['invoiceAddress'], $formats))) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            if ($isUseID) {
                if (!($address = UserAddress::model()->findByPk($tmp['addressID'])) || $address->deleted || $address->userID != $params['userID']) {
                    return F::errReturn(RC::RC_ADDRESS_NOT_EXISTS);
                }
                $params['invoiceAddressObj'] = $address;
            } else {
                if ($address = UserAddress::model()->findByAttributes($tmp, 'deleted=:deleted', array(':deleted' => UserAddress::DELETED_F))) {
                    $params['invoiceAddressObj'] = $address;
                    $tmp = array('addressID' => $address->id);
                }
            }
            $params['invoiceAddress'] = $tmp;
        }

        if (($params['passengerNum'] = count($params['passengers'])) > DictFlight::MAX_PASSENGER_NUM) {
            return F::errReturn(RC::RC_F_PASSENGER_NUM_ERROR);
        }
    
        //检测常用乘客人
        foreach ($params['passengers'] as $k => $passenger) {
            $isUseID = isset($passenger['passengerID']);
            if (!$isUseID) {
                $passenger['userID'] = $params['userID'];
            }
            $formats = $isUseID ? array('passengerID' => ParamsFormat::INTNZ) : UserPassenger::getCreateOrModifyFormats(True);
            if (!($tmp = F::checkParams($passenger, $formats))) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            if ($isUseID) {
                if (!($passenger = UserPassenger::model()->findByPk($tmp['passengerID'])) || $passenger->deleted || $passenger->userID != $params['userID']) {
                    return F::errReturn(RC::RC_PASSENGER_NOT_EXISTS);
                }
            } else {
                if ($tmpPassenger = UserPassenger::model()->findByAttributes($tmp, 'deleted=:deleted', array(':deleted' => UserPassenger::DELETED_F))) {
                    $passenger = $tmpPassenger;
                } else {
                    $passenger = $tmp;
                }
            }
            $params['passengers'][$k] = $passenger;
        }
        $passengers = self::classifyPassengers($params['passengers']);
    
        //检测往返航程、航段 array('routeKey' => 'ax8ands', 'segments' => array(array(...)));
        $params['segmentNum'] = $totalOrderPrice = $totalTicketPrice = $totalAirportTaxPrice = $totalOilTaxPrice = $totalInsurePrice = 0;
        $routeTypes = empty($params['isRound']) ? array('departRoute') : array('departRoute', 'returnRoute');
        foreach ($routeTypes as $routeType) {
            if (!($tmp = F::checkParams($params[$routeType], array(
                'departCityCode' => ParamsFormat::F_CN_CITY_CODE, 'arriveCityCode' => ParamsFormat::F_CN_CITY_CODE,
                'departDate' => ParamsFormat::DATE, 'routeKey' => ParamsFormat::MD5, 'segments' => ParamsFormat::ISARRAY
            )))) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }

            $routesParams = F::arrayGetByKeys($tmp, array('departCityCode', 'arriveCityCode', 'departDate'));
            if (!F::isCorrect($res = ProviderF::getCNFlightList($routesParams, True))) {
                return $res;
            }
    
            //检测有无此航程
            if (empty($res['data']['routes'][$tmp['routeKey']]) || count($res['data']['routes'][$tmp['routeKey']]['segments']) != count($tmp['segments'])) {
                return F::errReturn(RC::RC_F_ROUTE_NOT_EXISTS);
            }
    
            $realSegments = $res['data']['routes'][$tmp['routeKey']]['segments'];
            $realCarftMap = $res['data']['craftMap'];
            //检测此航程的航段信息是否正确
            foreach ($tmp['segments'] as $segmentIndex => $segment) {
                $params['segmentNum']++;
    
                if ($realSegments[$segmentIndex]['flightKey'] != ProviderF::getFlightKey($segment['departCityCode'], $segment['arriveCityCode'], $segment['flightNo'])) {
                    return F::errReturn(RC::RC_F_SEGMENT_ERROR);
                }

                $segmentParams = F::arrayGetByKeys($segment, array('departCityCode', 'arriveCityCode', 'flightNo'));
                $segmentParams['departDate'] = date('Y-m-d', $segment['departTime']);
                if (!F::isCorrect($res = ProviderF::getCNFlightDetail($segmentParams, True))) {
                    return $res;
                }

                $flightKey = ProviderF::getFlightKey($segment['departCityCode'], $segment['arriveCityCode'], $segment['flightNo']);
                if (empty($res['data']['flights'][$flightKey])) {
                    return F::errReturn(RC::RC_F_INFO_CHANGED);
                }
    
                $keys = array(
                    'flightNo', 'departCityCode', 'arriveCityCode', 'departAirportCode', 'arriveAirportCode', 'departTime', 'arriveTime',
                    'airlineCode', 'craftCode', 'adultAirportTax', 'adultOilTax', 'childAirportTax', 'childOilTax', 'babyAirportTax', 'babyOilTax'
                );
                $realSegment = $res['data']['flights'][$flightKey];
                if (F::arrayGetByKeys($segment, $keys) != F::arrayGetByKeys($realSegment, $keys)) {
                    return F::errReturn(RC::RC_F_INFO_CHANGED);
                }
                if (!isset($realSegment['cabins'][$segment['cabinInfo']['cabin']])) {
                    return F::errReturn(RC::RC_F_NO_SUCH_CABIN);
                }
    
                $keys = array('cabin', 'cabinClass', 'adultPrice', 'childPrice', 'babyPrice');
                $realCabin = $realSegment['cabins'][$segment['cabinInfo']['cabin']];
                if (F::arrayGetByKeys($segment['cabinInfo'], $keys) != F::arrayGetByKeys($realCabin, $keys)) {
                    return F::errReturn(RC::RC_F_INFO_CHANGED);
                }
    
                if ((!is_numeric($realCabin['cabinNum']) && $realCabin['cabinNum'] != 'A') || (is_numeric($realCabin['cabinNum']) && intval($realCabin['cabinNum']) < count($params['passengers']))) {
                    return F::errReturn(RC::RC_F_CABIN_NUM_ERROR);
                }
    
                //只要有一个是强制表现的就所有航段都购买保险，若要分开则不判断此步并修改totalInsurePrice计算方式，按照($realCabin['isforceInsure'] || $params['isInsured'])计算
                if ($realCabin['isForceInsure'] && !$params['isInsured']) {
                    return F::errReturn(RC::RC_MUST_INSURE);
                }
                $modifySegment = &$params[$routeType]['segments'][$segmentIndex];
                $modifySegment['departTerm'] = $realSegment['departTerm'];
                $modifySegment['arriveTerm'] = $realSegment['arriveTerm'];
                $modifySegment['cabinInfo']['cabinClassName'] = $realCabin['cabinClassName'];
                $modifySegment['craftType'] = DictFlight::CRAFT_LARGE;
                $modifySegment['isBack'] = $routeType == 'returnRoute' ? Dict::STATUS_TRUE : Dict::STATUS_FALSE;
                foreach ($realCarftMap as $craftTypeStr => $craftCodes) {
                    if (in_array($segment['craftCode'], $craftCodes)) {
                        $modifySegment['craftType'] = DictFlight::getCraftTypeByStr($craftTypeStr);
                        break;
                    }
                }
                $modifySegment = CMap::mergeArray($modifySegment, array_fill_keys(array('orderPrice', 'ticketPrice', 'airportTaxPrice', 'oilTaxPrice', 'insurePrice', 'invoicePrice'), 0));
                foreach (DictFlight::$ticketTypes as $ticketType => $ticketTypeConfig) {
                    $totalTicketPrice += count($passengers[$ticketType]) * $segment['cabinInfo'][$ticketTypeConfig['str'] . 'Price'];
                    $totalInsurePrice += intval($params['isInsured']) * DictFlight::INSURE_PRICE * count($passengers[$ticketType]);
                    $totalAirportTaxPrice += count($passengers[$ticketType]) * $segment[$ticketTypeConfig['str'] . 'AirportTax'];
                    $totalOilTaxPrice += count($passengers[$ticketType]) * $segment[$ticketTypeConfig['str'] . 'OilTax'];
                }
            }
        }
    
        //检测价格
        $params['price'] = F::checkParams($params['price'], array_fill_keys(array('orderPrice', 'ticketPrice', 'airportTaxPrice', 'oilTaxPrice', 'insurePrice', 'invoicePrice'), '!' . ParamsFormat::INTNZ . '--0'));
        $invoicePrice = intval($params['isInvoice']) * Dict::INVOICE_PRICE;
        $tmp = array(
            'orderPrice' => $totalTicketPrice + $totalAirportTaxPrice + $totalOilTaxPrice + $totalInsurePrice + $invoicePrice,
            'ticketPrice' => $totalTicketPrice,
            'airportTaxPrice' => $totalAirportTaxPrice,
            'oilTaxPrice' => $totalOilTaxPrice,
            'insurePrice' => $totalInsurePrice,
            'invoicePrice' =>  $invoicePrice
        );
        foreach ($tmp as $k => $v) {
            if ($v != $params['price'][$k]) {
                return F::errReturn(RC::RC_F_PRICE_ERROR);
            }
        }
    
        return F::corReturn($params);
    }
    
    public static function createOrder($params) {
        if (!F::isCorrect($res = self::_checkCreateOrderParams($params))) {
            return $res;
        }
        $params = $res['data'];
    
        $train = Yii::app()->db->beginTransaction();
        try {
            if (!isset($params['contacter']['contacterID'])) {
                if (!F::isCorrect($res = UserContacter::createContacter($params['contacter']))) {
                    throw new Exception($res['rc']);
                }
                $params['contacterObj'] = $res['data'];
            }
    
            if ($params['isInvoice'] && !isset($params['invoiceAddress']['addressID'])) {
                if (!F::isCorrect($res = UserAddress::createAddress($params['invoiceAddress']))) {
                    throw new Exception($res['rc']);
                }
                $params['invoiceAddressObj'] = $res['data'];
            }
    
            $passengers = array();
            foreach ($params['passengers'] as $index => $passenger) {
                if (!is_object($passenger)) {
                    if (!F::isCorrect($res = UserPassenger::createPassenger($passenger))) {
                        throw new Exception($res['rc']);
                    }
                    $passenger = $res['data'];
                }
                $passengers[] = $passenger;
            }
    
            $attributes = F::arrayGetByKeys($params, array('merchantID', 'userID', 'departmentID', 'companyID', 'isPrivate', 'isInsured', 'isInvoice', 'isRound', 'segmentNum', 'passengerNum', 'reason'));
            $attributes = array_merge($attributes, $params['price']);
            $attributes['contactName'] = $params['contacterObj']->name;
            $attributes['contactMobile'] = $params['contacterObj']->mobile;
            $attributes['invoiceAddress'] = !$params['isInvoice'] ? '' : $params['invoiceAddressObj']->getDescription();
            $attributes['passengers'] = self::concatPassengers($passengers);
            $attributes['status'] = $params['isPrivate'] ? FlightStatus::WAIT_PAY : FlightStatus::WAIT_CHECK;
            
            $order = new FlightCNOrder();
            $order->attributes = $attributes;
            if (!$order->save()) {
                Q::logModel($order);
                throw new Exception(RC::RC_MODEL_CREATE_ERROR);
            }
            
            $routeTypes = empty($params['isRound']) ? array('departRoute') : array('departRoute', 'returnRoute');
            foreach ($routeTypes as $routeType) {
                foreach ($params[$routeType]['segments'] as $segmentIndex => $segment) {
                    $record = F::arrayGetByKeys($segment, array(
                        'flightNo', 'departCityCode', 'arriveCityCode', 'departAirportCode', 'arriveAirportCode',
                        'departTime', 'arriveTime', 'departTerm', 'arriveTerm', 'airlineCode', 'craftCode', 'craftType', 'adultAirportTax', 'adultOilTax',
                        'childAirportTax', 'childOilTax', 'babyAirportTax', 'babyOilTax', 'isBack'
                    ));

                    $record = CMap::mergeArray($record, $segment['cabinInfo']);
                    $record['orderID'] = $order->id;
    
                    $segment = new FlightCNSegment();
                    $segment->attributes = $record;
                    if (!$segment->save()) {
                        Q::logModel($segment);
                        throw new Exception(RC::RC_MODEL_CREATE_ERROR);
                    }
                }
            }
    
            $train->commit();
            
            Log::add(Log::TYPE_CN_FLIGHT, $order->id, array('status' => $order->status, 'isSucc' => True));
            
            return F::corReturn($order);
        } catch (Exception $e) {
            $train->rollback();
            return F::errReturn($e->getMessage());
        }
    }
    
    public function getRoutes() {
        $rtn = array();
        $cities = DataAirport::getCNCities();
        $airports = DataAirport::getCNAiports();
        $airlines = DataAirline::getAirlines();
        foreach ($this->segments as $segment) {
            $routeType = $segment->isBack ? 'returnRoute' : 'departRoute';
            if (empty($rtn[$routeType])) {
                $rtn[$routeType] = array(
                    'segments' => array()
                );
            }
            
            $rtn[$routeType]['segments'][$segment->id] = $segment;
        }
        
        $routeTypes = $this->isRound ? array('returnRoute', 'departRoute') : array('departRoute');
        foreach ($routeTypes as $routeType) {
            $firstSegment = current($rtn[$routeType]['segments']);
            $lastSegment = end($rtn[$routeType]['segments']);
            $rtn[$routeType] = array_merge($rtn[$routeType], array(
                'departCityCode' => $firstSegment['departCityCode'], 'arriveCityCode' => $lastSegment['arriveCityCode'],
                'departAirportCode' => $firstSegment['departAirportCode'], 'arriveAirportCode' => $lastSegment['arriveAirportCode'],
                'departTime' => $firstSegment['departTime'], 'arriveTime' => $lastSegment['arriveTime']
            ));
        }
        
        return $rtn;
    }
    
    public static function search($params, $isGetCriteria = False, $isWithRoute = True) {
        $rtn = array('criteria' => Null, 'params' => array(), 'data' => array());
    
        $defaultBeginDate = date('Y-m-d', strtotime('-1 week'));
        $rtn['params'] = F::checkParams($params, array(
            'orderID' => '!' . ParamsFormat::INTNZ . '--0', 'userID' => '!' . ParamsFormat::INTNZ . '--0', 'departmentID' => '!' . ParamsFormat::INTNZ . '--0', 'companyID' => '!' . ParamsFormat::INTNZ . '--0', 'operaterID' => '!' . ParamsFormat::INTNZ . '--0',
            'beginDate' => '!' . ParamsFormat::DATE . '--' . $defaultBeginDate, 'endDate' => '!' . ParamsFormat::DATE . '--' . Q_DATE,
        ));
    
        $criteria = new CDbCriteria();
        $criteria->with = array_keys(self::model()->relations());
        $criteria->order = 't.id DESC';
        foreach (array('orderID', 'userID', 'departmentID', 'companyID', 'operaterID') as $type) {
            if (!empty($rtn['params'][$type])) {
                $field = $type == 'orderID' ? 'id' : $type;
                $criteria->compare('t.' . $field, $params[$type]);
            }
        }
        if (!empty($params['status'])) {
            if (!is_array($params['status'])) {
                $params['status'] = array($params['status']);
            }
            if (F::checkParams($params, array('status' => ParamsFormat::F_STATUS_ARRAY))) {
                $rtn['params']['status'] = $params['status'];
                $criteria->addInCondition('t.status', $rtn['params']['status']);
            }
        }
        if (isset($params['isPrivate'])) {
            $rtn['params']['isPrivate'] = intval($params['isPrivate']);
            $criteria->compare('t.isPrivate', $rtn['params']);
        }
        $criteria->addBetweenCondition('t.ctime', strtotime($rtn['params']['beginDate']), strtotime($rtn['params']['endDate'] . ' 23:59:59'));
        
        $rtn['criteria'] = $criteria;
        if ($isGetCriteria) {
            return $rtn;
        }
    
        $orders = F::arrayAddField(self::model()->findAll($criteria), 'id');
        foreach ($orders as $orderID => $order) {
            if ($isWithRoute) {
                $orders[$orderID]->routes = $order->getRoutes();
            }
        }
        $rtn['data'] = $orders;
        
        return $rtn;
    }
    
    private function _setCollectParams($status, $params, $isMerge = True) {
        $this->_collectParams[$status] = $isMerge ? CMap::mergeArray($this->_collectParams, $params) : $params;
    }
    
    private function _getCollectParams($status) {
        return isset($this->_collectParams[$status]) ? $this->_collectParams[$status] : array();
    }
    
    public function changeStatus($status, $params = array(), $condition = '', $conditionParams = array()) {
        if (!FlightStatus::isFlightStatus($status)) {
            return F::errReturn(RC::RC_STATUS_NOT_EXISTS);
        }

        $isUserOp = $isAdminHd = $isAdminOp = False;
        if (!FlightStatus::isJumpCheck($status)) {
            $isUserOp = FlightStatus::isUserOp($this->status, $status);
            $isAdminHd = FlightStatus::isAdminHd($this->status, $status);
            $isAdminOp = FlightStatus::isAdminOp($this->status, $status);
            if (!($isUserOp || $isAdminHd || $isAdminOp)) {
                return F::errReturn(RC::RC_STATUS_NOT_OP);
            }
            
            if (($isAdminHd || $isAdminOp) && empty($params['operaterID'])) {
                return F::errReturn(RC::RC_STATUS_NO_OPERATER);
            }
            
            if ($func = FlightStatus::getCheckFunc($status)) {
                if (!$this->$func()) {
                    return F::errReturn(RC::RC_STATUS_NOT_MATCH_CHECK);
                }
            }
        }
        
        $toStatusConfig = FlightStatus::$flightStatus[$status];
        $trans = Yii::app()->db->beginTransaction();
        try {
            $res = F::$return;
            $beforeMethodName = '_cS2' . $toStatusConfig['str'] . 'Before';
            $methodName = '_cS2' . $toStatusConfig['str'];
            $afterMethodName = '_cS2' . $toStatusConfig['str'] . 'After';
            
            $params['status'] = $status;
            $tmp = $isAdminHd || $isAdminOp ? array('status' => $status, 'operaterID' => $params['operaterID']) : array('status' => $status);
            $tmp = array('params' => $tmp, 'condition' => '', 'conditionParams' => array());
            if (method_exists($this, $beforeMethodName)) {
                if (F::isCorrect($res = $this->$beforeMethodName($params, $condition, $conditionParams))) {
                    if (!empty($res['data']) && is_array($res['data'])) {
                        $tmp = CMap::mergeArray($tmp, $res['data']);
                    }
                }
            }

            if (F::isCorrect($res)) {
                $func = method_exists($this, $methodName) ? $methodName : '_changeStatus';
                if (F::isCorrect($res = $this->$func($tmp['params'], $condition, $conditionParams)) && method_exists($this, $afterMethodName)) {
                    $res = $this->$afterMethodName();
                }
            }
            
            $func = F::isCorrect($res) ? 'commit' : 'rollback';
            $trans->$func();
        } catch (Exception $e) {
            Q::log($e->getMessage(), 'dberror.changeStatus');
            
            $trans->rollback();
            $res = F::errReturn(RC::RC_DB_ERROR);
        }
        
        $this->_setCollectParams($status, array(), False);
        Log::add(Log::TYPE_CN_FLIGHT, $this->id, array('status' => $this->status, 'isSucc' => F::isCorrect($res), 'params' => $params, 'res' => $res));
        
        return $res;
    }
    
    private function _changeStatus($sets, $condition = '', $conditionParams = array()) {
        $newSets = $sets;
        $sets = array();
        foreach ($newSets as $k => $v) {
            if ($this->hasAttribute($k)) {
                $sets[$k] = $v;
            }
        }

        $sets['utime'] = Q_TIME;
        if (FlightStatus::isAdminOp($this->status, $sets['status'])) {
            $condition .= empty($condition) ? '' : ' AND';
            $condition .= ' operaterID=:operaterID';
            $conditionParams[':operaterID'] = $sets['operaterID'];
        }
    
        $condition .= empty($condition) ? '' : ' AND';
        $condition .= ' status=:status';
        $conditionParams[':status'] = $this->status;
        if (!self::model()->updateByPk($this->id, $sets, $condition, $conditionParams)) {
            return F::errReturn(RC::RC_STATUS_CHANGE_ERROR);
        }
    
        $this->setAttributes($sets);
    
        return F::corReturn();
    }
    
    private function _checkBefore($params) {
        if (empty($params['reviewerID'])) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $rtn = array();
        if (!($params['reviewerID'] instanceof User)) {
            if (!($params['reviewerID'] = User::model()->findByPk($params['reviewerID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
                return F::errReturn(RC::RC_USER_NOT_EXISTS);
            }
        }
        
        if (!$params['reviewerID']->isReviewer || $this->departmentID != $params['reviewerID']->departmentID) {
            return F::errReturn(RC::RC_HAVE_NO_REVIEW_PRIVILEGE);
        }
        
        return F::corReturn(array('params' => array('reviewerID' => $params['reviewerID']->id)));
    }
    
    private function _cS2CheckFailBefore($params) {
        return $this->_checkBefore($params);
    }
    
    private function _cS2CheckSuccBefore($params) {
        return $this->_checkBefore($params);
    }
    
    private function _cS2PayedBefore($params) {
        if (!F::checkParams($params, array('payPrice' => ParamsFormat::INTNZ))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        return F::corReturn(array('params' => array('payPrice' => $params['payPrice'])));
    }
    
    private function _cS2BookFailBefore() {
        return $this->isPrivate ? FlightStatus::BOOK_FAIL_WAIT_RFD : FlightStatus::BOOK_FAIL;
    }
    
    private function _getCS2BookSuccFormats() {
        return array(
            'bigPNR' => '!' . ParamsFormat::F_PNR . '--',
            'smallPNR' => ParamsFormat::F_PNR,
            'ticketPrice' => ParamsFormat::FLOATNZ,
            'airportTax' => ParamsFormat::FLOAT,
            'oilTax' => ParamsFormat::FLOAT,
            'realTicketPrice' => ParamsFormat::FLOATNZ,
            'ticketNo' => ParamsFormat::F_TICKET_NO
        );
    }
    
    private function _cS2BookSuccBefore($params) {
        $ticketAttributes = F::arrayGetByKeys($this, array('userID', 'departmentID', 'companyID'));
        $ticketAttributes['orderID'] = $this->id;
        $ticketAttributes['ticketID'] = 0;
        $ticketAttributes['isInsured'] = $this->isInsured;
        
        $userTAOPrice = 0;
        $passengers = self::parsePassengers($this->passengers);
        foreach ($this->segments as $segment) {
            if (empty($params['segments'][$segment->id]) || !is_array($params['segments'][$segment->id])) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            $segmentParams = $params['segments'][$segment->id];
            $ticketAttributes = array_merge($ticketAttributes, F::arrayGetByKeys($segment, array('flightNo', 'craftType', 'craftCode')));
            $ticketAttributes['segmentID'] = $segment->id;
            foreach ($passengers as $passengerID => $passenger) {
                if (empty($segmentParams[$passengerID]) || !($passengerParams = F::checkParams($segmentParams[$passengerID], $this->_getCS2BookSuccFormats()))) {
                    return F::errReturn(RC::RC_VAR_ERROR);
                }
            
                $ticketAttributes['ticketPrice'] = $passengerParams['ticketPrice'] * 100;
                $ticketAttributes['airportTax'] = $passengerParams['airportTax'] * 100;
                $ticketAttributes['oilTax'] = $passengerParams['oilTax'] * 100;
                $ticketAttributes['realTicketPrice'] = $passengerParams['realTicketPrice'] * 100;
                $ticketAttributes = array_merge($ticketAttributes, F::arrayGetByKeys($passengerParams, array('bugPNR', 'smallPNR', 'ticketNo')));
                $ticketAttributes['passenger'] = self::concatPassenger($passenger);
                $ticketAttributes['insurePrice'] = $this->insurePrice / $this->passengerNum;
                $ticketAttributes['resignHandlePrice'] = $ticketAttributes['refundHandlePrice'] = $ticketAttributes['realResignHandlePrice'] = $ticketAttributes['realRefundHandlePrice'] = $ticketAttributes['refundPrice'] = $ticketAttributes['payPrice'] = 0;
                $ticketAttributes = array_merge($ticketAttributes, F::arrayGetByKeys($segment, array('cabin', 'cabinClass', 'cabinClassName', 'departTime', 'arriveTime', 'departTerm', 'arriveTerm')));
                $ticketAttributes['status'] = FlightStatus::BOOK_SUCC;
            
                $userTAOPrice += $ticketAttributes['ticketPrice'] + $ticketAttributes['airportTax'] + $ticketAttributes['oilTax'];
            
                $ticket = new FlightCNTicket();
                $ticket->attributes = $ticketAttributes;
                if (!$ticket->save()) {
                    Q::logModel($ticket);
                    return F::errReturn(RC::RC_MODEL_CREATE_ERROR);
                }
            }
        }
        
        if (!$this->isPrivate) {
            $info = array('orderID' => $this->id, 'departmentName' => $this->department->name, 'userName' => $this->user->name);
            $company = Company::model()->findByPk($this->companyID);
            if (
                !F::isCorrect($res = $company->changeFinance(CompanyFinanceLog::TYPE_ORDER_PRICE, $this->id, $userTAOPrice, 0, $info)) ||
                !F::isCorrect($res = $company->changeFinance(CompanyFinanceLog::TYPE_INSURE_PRICE, $this->id, $this->insurePrice, 0, $info)) ||
                !F::isCorrect($res = $company->changeFinance(CompanyFinanceLog::TYPE_INVOICE_PRICE, $this->id, $this->invoicePrice, 0, $info))
            ) {
                return $res;
            }
        }
        
        return F::corReturn();
    }
    
    private function _cS2BookSuccAfter() {
        //短信通知
        $airports = ProviderF::getCNAirportList();
        $passengers = implode(',', F::arrayGetField(self::parsePassengers($this->passengers), 'name'));
        $routes = $this->getRoutes();
        $routeTypes = $this->isRound ? array('departRoute', 'returnRoute') : array('departRoute');
        foreach ($routeTypes as $routeType) {
            $route = $routes[$routeType];
            $firstSegment = current($route['segments']);
            $params = array(
                'mobile' => $this->contactMobile,
                'departAirport' => $airports[$route['departAirportCode']]['airportName'],
                'arriveAirport' => $airports[$route['arriveAirportCode']]['airportName'],
                'flightNo' => $firstSegment['flightNo'],
                'departDate' => date('m月d日', $route['departTime']),
                'departTime' => date('H:i', $route['departTime']),
                'arriveTime' => date('H:i', $route['arriveTime']),
                'passengers' => $passengers
            );
            
            SMS::send($params, SMSTemplate::F_BOOK_SUCC);
        }
        
        //APP推送 **需要改掉**
        $title = '订单提示'; $text= "尊敬的客户您好, 恭喜您的订单{$this->id}出票成功, 点击查看详情";
        $params = array('behaviorType'=>'V001', 'orderID'=>$this->id);
        $user = User::model()->findByPk($this->userID);
        switch ($user->deviceType) {
            case 1:
                $push = new Push(Q::PUSH_IOS_KEY, Q::PUSH_IOS_SECRET);
                $push->sendIOSUnicast($text, $user->deviceToken, $params);
                break;
            case 2:
                $push = new Push(Q::PUSH_ANDROID_KEY, Q::PUSH_ANDROID_SECRET);
                $push->sendAndroidUnicast($title, $text, $user->deviceToken, $params);
                break;
        }
        
        return F::corReturn();
    }
    
    private function _getCS2RsnAgreeFormats() {
        return array(
            'tickets' => ParamsFormat::ISARRAY,
            'flightNo' => ParamsFormat::F_FLIGHT_NO,
            'cabinClass' => ParamsFormat::F_CABIN_CLASS,
            'departTime' => ParamsFormat::DATEHM,
            'arriveTime' => ParamsFormat::DATEHM,
            'isInsured' => '!' . ParamsFormat::BOOL . '--0',
        );
    }
    
    private function _getCS2RsnAgreeTicketFormats() {
        return array(
            'ticketPrice' => ParamsFormat::FLOATNZ,
            'airportTax' => ParamsFormat::FLOAT,
            'oilTax' => ParamsFormat::FLOAT,
            'resignHandlePrice' => ParamsFormat::FLOAT
        );
    }
    
    private function _cS2RsnAgreeBefore($params) {
        if (!($params = F::checkParams($params, $this->_getCS2RsnAgreeFormats())) || count($params['tickets']) <= 0) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $segmentID = 0;
        $payPrice = 0;
        $tickets = F::arrayAddField($this->tickets, 'id');
        $segments = F::arrayAddField($this->segments, 'id');
        foreach ($params['tickets']  as $ticketID => $ticketParams) {
            if (!F::checkParams($ticketParams, $this->_getCS2RsnAgreeTicketFormats())) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            if (!isset($tickets[$ticketID])) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            $ticket = $tickets[$ticketID];
            if (!in_array($ticket->status, FlightStatus::getCanResignTicketStatus())) {
                return F::errReturn(RC::RC_STATUS_ERROR);
            }
            
            $passenger = self::parsePassenger($ticket->passenger);
            $segmentID = $segmentID == 0 ? $ticket->segmentID : $segmentID; //只允许改签一个航段
            if ($ticket->segmentID != $segmentID) {
                return F::errReturn(RC::RC_F_RESIGN_ONLY_ONE_SEGMENT);
            }
            $segment = $segments[$segmentID];

            foreach (array('ticketPrice', 'airportTax', 'oilTax', 'resignHandlePrice') as $priceKey) {
                $ticketParams[$priceKey] *= 100; 
            }
            
            $attributes = F::arrayGetByKeys($ticket, array('userID', 'departmentID', 'companyID', 'orderID', 'segmentID', 'passenger', 'bitPNR', 'smallPNR', 'ticketNo'));
            $attributes = array_merge($attributes, F::arrayGetByKeys($params, array('flightNo', 'cabin', 'cabinClass', 'isInsured')));
            $attributes = array_merge($attributes, F::arrayGetByKeys($segment, array('cabin', 'craftCode', 'craftType', 'departTerm', 'arriveTerm')));
            $attributes = array_merge($attributes, F::arrayGetByKeys($ticketParams, array('ticketPrice', 'airportTax', 'oilTax', 'resignHandlePrice')));
            $attributes = array_merge($attributes, array_fill_keys(array('realTicketPrice', 'refundHandlePrice'), 0));
            $attributes['insurePrice'] = intval($attributes['isInsured']) * DictFlight::INSURE_PRICE;
            $attributes['payPrice'] = $attributes['ticketPrice'] + $attributes['airportTax'] + $attributes['oilTax'] + $attributes['resignHandlePrice'] + $attributes['insurePrice']
                                    - $ticket->ticketPrice - $ticket->airportTax - $ticket->oilTax;
            $attributes['payPrice'] = max($attributes['payPrice'], 0);
            $attributes['ticketID'] = $ticket->id;
            $attributes['cabinClassName'] = DictFlight::$cabinClasses[$attributes['cabinClass']]['name'];
            $attributes['departTime'] = strtotime($params['departTime']);
            $attributes['arriveTime'] = strtotime($params['arriveTime']);
            $attributes['status'] = FlightStatus::RSN_AGREE;
            
            if (!FlightCNTicket::model()->updateByPk($ticket->id, array('status' => FlightStatus::RSN_RSNEDING), 'status=:status', array(':status' => $ticket->status))) {
                Q::logModel($ticket);
                return F::errReturn(RC::RC_STATUS_TICKET_CHANGE_ERROR);
            }
            
            $newTicket = new FlightCNTicket();
            $newTicket->attributes = $attributes;
            if (!$newTicket->save()) {
                Q::logModel($newTicket);
                return F::errReturn(RC::RC_MODEL_CREATE_ERROR);
            }
            
            $payPrice += $attributes['payPrice'];
        }
        
        $status = !$this->isPrivate ? FlightStatus::RSN_AGREE : ($payPrice > 0 ? FlightStatus::RSN_NED_PAY : FlightStatus::RSN_PAYED);
        return F::corReturn(array('params' => array('status' => $status)));
    }
    
    private function _cS2RsnRefuseBefore($params) {
        return F::corReturn(array('params' => array('status' => FlightStatus::BOOK_SUCC)));
    }
    
    private function _getCS2RsnSuccFormats() {
        return array(
            'smallPNR' => ParamsFormat::F_PNR,
            'realTicketPrice' => ParamsFormat::FLOATNZ,
            'realResignHandlePrice' => ParamsFormat::FLOAT,
            'ticketNo' => ParamsFormat::F_TICKET_NO
        );
    }
    
    private function _cS2RsnSuccBefore($params) {
        if (!F::checkParams($params, array('tickets' => ParamsFormat::ISARRAY)) || count($params['tickets']) <= 0) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }

        $rsnPrice = $insurePrice = 0;
        $logPassengers = array();
        foreach ($this->tickets as $ticket) {
            if ($ticket->status != FlightStatus::RSN_AGREE) {
                continue;
            }
            
            if (empty($params['tickets'][$ticket->id]) || !($attributes = F::checkParams($params['tickets'][$ticket->id], $this->_getCS2RsnSuccFormats()))) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            $attributes['realTicketPrice'] *= 100;
            $attributes['realResignHandlePrice'] *= 100;
            $attributes['status'] = FlightStatus::RSN_SUCC;
            if (!FlightCNTicket::model()->updateByPk($ticket->id, $attributes, 'status=:status', array(':status' => FlightStatus::RSN_AGREE))) {
                return F::errReturn(RC::RC_STATUS_TICKET_CHANGE_ERROR);
            }
            
            if (!FlightCNTicket::model()->updateByPk($ticket->ticketID, array('status' => FlightStatus::RSNED), 'status=:status', array(':status' => FlightStatus::RSN_RSNEDING))) {
                return F::errReturn(RC::RC_STATUS_TICKET_CHANGE_ERROR);
            }
            
            $insurePrice += $ticket->insurePrice;
            $rsnPrice += $ticket->payPrice;
            
            $passenger = self::parsePassenger($ticket->passenger);
            $logPassengers[] = $passenger['name'];
        }
        
        if (!$this->isPrivate) {
            $info = array('orderID' => $this->id, 'departmentName' => $this->department->name, 'userName' => $this->user->name);
            $company = Company::model()->findByPk($this->companyID);
            if (
                !F::isCorrect($res = $company->changeFinance(CompanyFinanceLog::TYPE_RESIGN_PRICE, $this->id, $rsnPrice - $insurePrice, 0, array_merge($info, array('passengers' => implode('、', $logPassengers))))) ||
                !F::isCorrect($res = $company->changeFinance(CompanyFinanceLog::TYPE_INSURE_PRICE, $this->id, $insurePrice, 0, $info))
            ) {
                return $res;
            }
        }
        
        $num = FlightCNTicket::model()->countByAttributes(array('orderID' => $this->id, 'status' => FlightStatus::BOOK_SUCC));
        $status = $num > 0 ? FlightStatus::BOOK_SUCC : FlightStatus::RSNED;
        
        return F::corReturn(array('params' => array('status' => $status)));
    }
    
    private function _cS2RfdAgreeBefore($params) {
        if (!F::checkParams($params, array('tickets' => ParamsFormat::ISARRAY)) || count($params['tickets']) <= 0) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $tickets = F::arrayAddField($this->tickets, 'id');
        foreach ($params['tickets'] as $ticketID => $price) {
            if (!isset($tickets[$ticketID]) || !F::checkParams($price, array('refundHandlePrice' => ParamsFormat::FLOAT, 'realRefundHandlePrice' => ParamsFormat::FLOAT))) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            $ticket = $tickets[$ticketID];
            if (!in_array($ticket->status, FlightStatus::getCanRefundTicketStatus())) {
                return F::errReturn(RC::RC_STATUS_ERROR);
            }
            
            $attributes = array('status' => FlightStatus::RFD_AGREE, 'refundHandlePrice' => $price['refundHandlePrice'] * 100, 'realRefundHandlePrice' => $price['realRefundHandlePrice'] * 100);
            if (!FlightCNTicket::model()->updateByPk($ticket->id, $attributes, 'status=:status', array(':status' => $ticket->status))) {
                return F::errReturn(RC::RC_STATUS_TICKET_CHANGE_ERROR);
            }
        }
        
        $classifyTickets = self::classifyTickets(FlightCNTicket::model()->findAllByAttributes(array('orderID' => $this->id)));
        $isCanResgin = $isCanRefund = False;
        foreach ($classifyTickets as $status => $tickets) {
            if (in_array($status, FlightStatus::getCanResignTicketStatus())) {
                $isCanResgin = True;
            } elseif (in_array($status, FlightStatus::getCanRefundTicketStatus())) {
                $isCanRefund = True;
            }
        }
        
        $rtnStatus = $isCanResgin ? FlightStatus::BOOK_SUCC : ($isCanRefund ? FlightStatus::RSNED : FlightStatus::RFD_AGREE);
        return F::corReturn(array('params' => array('status' => $rtnStatus)));
    }
    
    private function _cS2RfdedBefore($params) {
        if (!F::checkParams($params, array('tickets' => ParamsFormat::ISARRAY)) || count($params['tickets']) <= 0) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $tickets = F::arrayAddField($this->tickets, 'id');
        $totalRefundPrice = 0;
        $passengers = array();
        foreach ($params['tickets'] as $ticketID => $refundPrice) {
            if (empty($tickets[$ticketID])) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            $ticket = $tickets[$ticketID];
            if (!in_array($ticket->status, FlightStatus::getCanRefundedTicketStatus())) {
                return F::errReturn(RC::RC_STATUS_ERROR);
            }
            $passenger = self::parsePassenger($ticket->passenger);
            $passengers[] = $passenger['name'];
            
            $refundPrice = $refundPrice * 100;
            if (!FlightCNTicket::model()->updateByPk($ticket->id, array('status' => FlightStatus::RFDED, 'refundPrice' => $refundPrice), 'status=:status', array(':status' => $ticket->status))) {
                return F::errReturn(RC::RC_STATUS_TICKET_CHANGE_ERROR);
            }
            
            $totalRefundPrice += $refundPrice;
        }
        
        if (!$this->isPrivate) {
            $info = array('orderID' => $this->id, 'departmentName' => $this->department->name, 'userName' => $this->user->name, 'passengers' => implode('、', $passengers));
            if (!F::isCorrect($res = $this->company->changeFinance(CompanyFinanceLog::TYPE_REFUND, $this->id, 0, $totalRefundPrice, $info))) {
                return $res;
            }
        }
        
        $classifyTickets = self::classifyTickets(FlightCNTicket::model()->findAllByAttributes(array('orderID' => $this->id)));
        $isCanResgin = $isCanRefund = False;
        foreach ($classifyTickets as $status => $tickets) {
            if (in_array($status, FlightStatus::getCanResignTicketStatus())) {
                $isCanResgin = True;
            } elseif (in_array($status, FlightStatus::getCanRefundTicketStatus())) {
                $isCanRefund = True;
            }
        }
        
        $rtnStatus = $isCanResgin ? FlightStatus::BOOK_SUCC : ($isCanRefund ? FlightStatus::RSNED : FlightStatus::RFDED);
        
        return F::corReturn(array('params' => array('status' => $rtnStatus)));
    }
    
    //对公-审核成功-邮件机票组
    private function _cS2CheckSuccAfter() {
        $cpl['tplInfo']['orderID'] = $this->id ;
        @Mail::sendMail($cpl, 'CheckSucc');
        return F::corReturn();
    }
    
    //对私-支付成功-邮件机票组
    private function _cS2PayedAfter() {
        $cpl['tplInfo']['orderID'] = $this->id ;
        @Mail::sendMail($cpl, 'Payed');
        return F::corReturn();
    }
    
    //申请退票成功-邮件机票组
    private function _cS2ApplyRfdAfter() {
        $cpl['tplInfo']['orderID'] = $this->id ;
        @Mail::sendMail($cpl, 'ApplyRfd');
        return F::corReturn();
    }
    
    //申请改签成功-邮件机票组
    private function _cS2ApplyRsnAfter() {
        $cpl['tplInfo']['orderID'] = $this->id ;
        @Mail::sendMail($cpl, 'ApplyRsn');
        return F::corReturn();
    }
}