<?php
class TrainOrder extends QActiveRecord {
    private $_collectParams = array();
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{trainOrder}}';
    }

    public function rules() {
        return array(
            array('merchantID, userID, departmentID, companyID, contactName, contactMobile, isPrivate, isInsured, isInvoice, isRound, passengers, passengerNum, orderPrice, ticketPrice, insurePrice, invoicePrice', 'required'),
            array('merchantID, userID, departmentID, companyID, reviewerID, isPrivate, isInsured, isInvoice, isRound, passengerNum, orderPrice, payPrice, ticketPrice, insurePrice, invoicePrice, invoicePostPrice, invoicePostID, operaterID, status, ctime, utime', 'numerical', 'integerOnly' => True),
            array('providerOID', 'length', 'max' => 15),
            array('contactName', 'length', 'max' => 50),
            array('contactMobile', 'length', 'max' => 11),
            array('passengers', 'length', 'max' => 655),
            array('invoiceAddress, reason', 'length', 'max' => 500),
            array('invoiceTradeNo, tradeNo', 'length', 'max' => 32),
            array('id, merchantID, providerOID, userID, departmentID, companyID, contactName, contactMobile, reviewerID, isPrivate, isInsured, isInvoice, isRound, passengers, passengerNum, orderPrice, payPrice, ticketPrice, insurePrice, invoicePrice, invoiceAddress, invoicePostPrice, invoicePostID, invoiceTradeNo, tradeNo, reason, operaterID, status, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'userID'),
            'department' => array(self::BELONGS_TO, 'Department', 'departmentID'),
            'company' => array(self::BELONGS_TO, 'Company', 'companyID'),
            'routes' => array(self::HAS_MANY, 'TrainRoute', 'orderID'),
            'tickets' => array(self::HAS_MANY, 'TrainTicket', 'orderID'),
            'reviewer' => array(self::BELONGS_TO, 'User', 'reviewerID'),
            'operater' => array(self::BELONGS_TO, 'BossAdmin', 'operaterID')
        );
    }
    
    public function getRoutes() {
        $rtn = array();
        $stations = ProviderT::getStationList();
        foreach ($this->routes as $route) {
            $routeType = $route->isBack ? 'returnRoute' : 'departRoute';
            $rtn[$routeType] = $route;
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
                $tmp['businessID'] = Dict::BUSINESS_TRAIN;
                $attributes = $tmp;
                $attributes['trainType'] = $attributes['type'];
                unset($attributes['type'], $attributes['businessID']);
                if ($attributes['trainType'] != Dict::PASSENGER_TYPE_CHILD && ($tmpPassenger = UserPassenger::model()->findByAttributes($attributes, 'deleted=:deleted', array(':deleted' => UserPassenger::DELETED_F)))) {
                    $passenger = $tmpPassenger;
                } else {
                    $passenger = $tmp;
                }
            }
            $params['passengers'][$k] = $passenger;
        }
        $passengers = UserPassenger::classifyPassengers($params['passengers'], Dict::BUSINESS_TRAIN);
        
        $totalTicketPrice = $totalInsurePrice = 0;
        $routeTypes = empty($params['isRound']) ? array('departRoute') : array('departRoute', 'returnRoute');
        foreach ($routeTypes as $routeType) {
            if (!($tmp = F::checkParams($params[$routeType], array(
                'departStationCode' => ParamsFormat::T_STATION_CODE, 'arriveStationCode' => ParamsFormat::T_STATION_CODE,
                'departTime' => ParamsFormat::TIMESTAMP, 'trainNo' => ParamsFormat::T_TRAIN_NO, 'seatType' => ParamsFormat::INTNZ,
                'seatPrice' => ParamsFormat::INTNZ
            )))) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            $routesParams = F::arrayGetByKeys($tmp, array('departStationCode', 'arriveStationCode'));
            $routesParams['departDate'] = date('Y-m-d', $tmp['departTime']);
            if (!F::isCorrect($res = ProviderT::getTrainList($routesParams))) {
                return $res;
            }
            
            if (empty($res['data'][$tmp['trainNo']])) {
                return F::errReturn(RC::RC_T_TRAIN_NOT_EXISTS);
            }
            
            $realTrainInfo = $res['data'][$tmp['trainNo']];
            if (empty($realTrainInfo['seats'][$tmp['seatType']])) {
                return F::errReturn(RC::RC_T_SEAT_NOT_EXISTS);
            }
            
            $realSeat = $realTrainInfo['seats'][$tmp['seatType']];
            if ($realSeat['seatPrice'] != $tmp['seatPrice']) {
                return F::errReturn(RC::RC_T_SEAT_PRICE_ERROR);
            }
            
            if ($realTrainInfo['departTime'] != $tmp['departTime']) {
                return F::errReturn(RC::RC_T_TRAIN_INFO_ERROR);
            }
            
            $totalTicketPrice += $params['passengerNum'] * $realSeat['seatPrice'];
            $totalInsurePrice += intval($params['isInsured']) * DictTrain::INSURE_PRICE * $params['passengerNum'];
            
            $params[$routeType]['arriveTime'] = $realTrainInfo['arriveTime'];
            $params[$routeType]['ticketPrice'] = $realSeat['seatPrice'];
            $params[$routeType]['isBack'] = $routeType == 'departRoute' ? 0 : 1;
        }
        
        //检测价格
        $params['price'] = F::checkParams($params['price'], array_fill_keys(array('orderPrice', 'ticketPrice', 'insurePrice', 'invoicePrice'), '!' . ParamsFormat::INTNZ . '--0'));
        $invoicePrice = intval($params['isInvoice']) * Dict::INVOICE_PRICE;
        $tmp = array(
            'orderPrice' => $totalTicketPrice + $totalInsurePrice + $invoicePrice,
            'ticketPrice' => $totalTicketPrice,
            'insurePrice' => $totalInsurePrice,
            'invoicePrice' =>  $invoicePrice
        );
        foreach ($tmp as $k => $v) {
            if ($v != $params['price'][$k]) {
                return F::errReturn(RC::RC_T_PRICE_ERROR);
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
                    if (Dict::PASSENGER_TYPE_CHILD != $passenger['type']) {
                        if (!F::isCorrect($res = UserPassenger::createPassenger($passenger))) {
                            throw new Exception($res['rc']);
                        }
                        $passenger = $res['data'];
                    }
                }
                $passenger['id'] = $index + 1;
                $passengers[] = $passenger;
            }
        
            $attributes = F::arrayGetByKeys($params, array('merchantID', 'userID', 'departmentID', 'companyID', 'isPrivate', 'isInsured', 'isInvoice', 'isRound', 'passengerNum', 'reason'));
            $attributes = array_merge($attributes, $params['price']);
            $attributes['contactName'] = $params['contacterObj']->name;
            $attributes['contactMobile'] = $params['contacterObj']->mobile;
            $attributes['invoiceAddress'] = !$params['isInvoice'] ? '' : $params['invoiceAddressObj']->getDescription();
            $attributes['passengers'] = UserPassenger::concatPassengers($passengers, Dict::BUSINESS_TRAIN);
            $attributes['status'] = $params['isPrivate'] ? TrainStatus::WAIT_PAY : TrainStatus::WAIT_CHECK;
        
            $order = new TrainOrder();
            $order->attributes = $attributes;
            if (!$order->save()) {
                Q::logModel($order);
                throw new Exception(RC::RC_MODEL_CREATE_ERROR);
            }
        
            $routeTypes = empty($params['isRound']) ? array('departRoute') : array('departRoute', 'returnRoute');
            foreach ($routeTypes as $routeType) {
                $record = F::arrayGetByKeys($params[$routeType], array(
                    'trainNo', 'departStationCode', 'arriveStationCode', 'departTime', 'arriveTime', 'seatType', 'ticketPrice', 'isBack'
                ));
                $record['orderID'] = $order->id;
                
                $route = new TrainRoute();
                $route->attributes = $record;
                if (!$route->save()) {
                    Q::logModel($route);
                    throw new Exception(RC::RC_MODEL_CREATE_ERROR);
                }
            }
        
            $train->commit();
        
            Log::add(Log::TYPE_TRAIN, $order->id, array('status' => $order->status, 'isSucc' => True));
        
            return F::corReturn($order);
        } catch (Exception $e) {
            $train->rollback();
            return F::errReturn($e->getMessage());
        }
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
        if (!TrainStatus::isTrainStatus($status)) {
            return F::errReturn(RC::RC_STATUS_NOT_EXISTS);
        }
    
        $isUserOp = $isSysHd = $isSysOp = False;
        if (!TrainStatus::isJumpCheck($status)) {
            $isUserOp = TrainStatus::isUserOp($this->status, $status);
            $isSysHd = TrainStatus::isSysHd($this->status, $status);
            $isSysOp = TrainStatus::isSysOp($this->status, $status);
            if (!($isUserOp || $isSysHd || $isSysOp)) {
                return F::errReturn(RC::RC_STATUS_NOT_OP);
            }
    
            if ($isSysHd || $isSysOp) {
                $params['operaterID'] = Dict::OPERATER_SYSTEM;
            }
            
            if (($isSysHd || $isSysOp) && empty($params['operaterID'])) {
                return F::errReturn(RC::RC_STATUS_NO_OPERATER);
            }
    
            if ($func = TrainStatus::getCheckFunc($status)) {
                if (!$this->$func()) {
                    return F::errReturn(RC::RC_STATUS_NOT_MATCH_CHECK);
                }
            }
        }
    
        $toStatusConfig = TrainStatus::$trainStatus[$status];
        $trans = Yii::app()->db->beginTransaction();
        try {
            $res = F::$return;
            $beforeMethodName = '_cS2' . $toStatusConfig['str'] . 'Before';
            $methodName = '_cS2' . $toStatusConfig['str'];
            $afterMethodName = '_cS2' . $toStatusConfig['str'] . 'After';
    
            $params['status'] = $status;
            $tmp = $isSysHd || $isSysOp ? array('status' => $status, 'operaterID' => $params['operaterID']) : array('status' => $status);
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
        if (TrainStatus::isSysOp($this->status, $sets['status'])) {
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
    
    private function _cS2BookPushedBefore($params) {
        if (!F::isCorrect($res = ProviderT::book($this))) {
            return $res;
        }
        
        return F::corReturn(array('params' => array('providerOID' => $res['data']['orderID'])));
    }
    
    private function _getBookSuccFormats() {
        return array(
            'trainNo' => ParamsFormat::T_TRAIN_NO,
            'departStationCode' => ParamsFormat::T_STATION_CODE,
            'arriveStationCode' => ParamsFormat::T_STATION_CODE,
            'departDateTime' => ParamsFormat::DATEHM,
            'arriveDateTime' => '!' . ParamsFormat::DATEHM . '--' . date('Y-m-d H:i'),
            'passengers' => ParamsFormat::ISARRAY,
            'pickNo' => ParamsFormat::T_TICKET_NO,
            'servicePrice' => ParamsFormat::FLOAT,
        );
    }
    
    private function _getBookSuccPassengerFormats() {
        return array(
            'id' => ParamsFormat::TEXTNZ,
            'type' => ParamsFormat::PASSENGER_TYPE,
            'cardType' => ParamsFormat::CARD_TYPE,
            'cardNo' => ParamsFormat::CARD_NO,
            'seatType' => ParamsFormat::T_SEAT_TYPE,
            'seatName' => ParamsFormat::TEXTNZ,
            'price' => ParamsFormat::FLOATNZ
        );
    }
    
    private function _cS2BookSuccBefore($params) {
        if (!F::checkParams($params, $this->_getBookSuccFormats())) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $route = $this->routes[0];
        $attributes = F::arrayGetByKeys($this, array('userID', 'departmentID', 'companyID'));
        $attributes['orderID'] = $this->id;
        $attributes['routeID'] = $route->id;
        $totalTicketPrice = 0;
        foreach (UserPassenger::parsePassengers($this->passengers) as $realPassenger) {
            $isMatch = False;
            foreach ($params['passengers'] as $index => $passenger) {
                if (!F::checkParams($passenger, $this->_getBookSuccPassengerFormats())) {
                    return F::errReturn(RC::RC_VAR_ERROR);
                }
                
                if ($passenger['cardNo'] == $realPassenger['cardNo'] && $passenger['type'] == $realPassenger['type']) {
                    $isMatch = True;
                    break;
                }
            }
            
            if (!$isMatch) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            unset($params['passengers'][$index]);
            
            if ($route['trainNo'] != $params['trainNo'] || $route['departStationCode'] != $params['departStationCode'] || $route['arriveStationCode'] != $params['arriveStationCode'] || $route['seatType'] != $passenger['seatType'] || $route['departTime'] > strtotime($params['departDateTime'])) {
                return F::errReturn(RC::RC_T_TRAIN_INFO_ERROR);
            }
            
            $attributes = array_merge($attributes, F::arrayGetByKeys($route, array(
                'trainNo', 'departStationCode', 'arriveStationCode'
            )));
            $attributes['providerPassengerID'] = $passenger['id'];
            $attributes['ticketInfo'] = $passenger['seatName'];
            $attributes['ticketNo'] = $params['pickNo'];
            $attributes['departTime'] = strtotime($params['departDateTime']);
            $attributes['arriveTime'] = Q_TIME;
            $attributes['ticketPrice'] = $passenger['price'] * 100;
            $attributes['insurePrice'] = $this->insurePrice / $this->passengerNum;
            $attributes['passenger'] = UserPassenger::concatPassenger($realPassenger);
            $attributes['seatType'] = $passenger['seatType'];
            $attributes['status'] = TrainStatus::BOOK_SUCC;
            $totalTicketPrice += $attributes['ticketPrice'];
            
            $ticket = new TrainTicket();
            $ticket->attributes = $attributes;
            if (!$ticket->save()) {
                Q::logModel($ticket);
                return F::errReturn(RC::RC_MODEL_CREATE_ERROR);
            }
        }
        
        if (!$this->isPrivate) {
            $info = array('orderID' => $this->id, 'departmentName' => $this->department->name, 'userName' => $this->user->name);
            $company = Company::model()->findByPk($this->companyID);
            if (
                !F::isCorrect($res = $company->changeFinance(CompanyFinanceLog::TYPE_ORDER_PRICE, $this, $totalTicketPrice, 0, $info)) ||
                !F::isCorrect($res = $company->changeFinance(CompanyFinanceLog::TYPE_INSURE_PRICE, $this, $this->insurePrice, 0, $info)) ||
                !F::isCorrect($res = $company->changeFinance(CompanyFinanceLog::TYPE_INVOICE_PRICE, $this, $this->invoicePrice, 0, $info))
            ) {
                return $res;
            }
        }
        
        return F::corReturn(array('params' => array('pickNo' => $params['pickNo'])));
    }
    
    private function _cS2ApplyRfdBefore($params) {
        if (!F::checkParams($params, array('ticketIDs' => ParamsFormat::ISARRAY))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $tickets = F::arrayAddField($this->tickets, 'id');
        foreach ($params['ticketIDs'] as $ticketID) {
            if (empty($tickets[$ticketID])) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            $ticket = $tickets[$ticketID];
            if (!in_array($ticket->status, TrainStatus::getCanRefundTicketStatus())) {
                return F::errReturn(RC::RC_STATUS_TICKET_ERROR);
            }
            
            $attributes = array('status' => TrainStatus::APPLY_RFD, 'utime' => Q_TIME);
            if (!TrainTicket::model()->updateByPk($ticket->id, $attributes, 'status=:status', array(':status' => $ticket->status))) {
                return F::errReturn(RC::RC_STATUS_TICKET_CHANGE_ERROR);
            }
        }
        
        return F::corReturn(array('params' => array('status' => $this->status)));
    }
    
    private function _cS2RfdPushedBefore($params) {
        if (!F::checkParams($params, array('ticketID' => ParamsFormat::INTNZ))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $tickets = F::arrayAddField($this->tickets, 'id');
        if (empty($tickets[$params['ticketID']])) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $ticket = $tickets[$params['ticketID']];
        if (!in_array($ticket->status, TrainStatus::getCanRefundTicketStatus())) {
            return F::errReturn(RC::RC_STATUS_TICKET_ERROR);
        }
        
        if (!F::isCorrect($res = ProviderT::refund($this, $ticket))) {
            return $res;
        }
        
        $attributes = array('status' => TrainStatus::RFD_PUSHED, 'utime' => Q_TIME);
        if (!TrainTicket::model()->updateByPk($ticket->id, $attributes, 'status=:status', array(':status' => $ticket->status))) {
            return F::errReturn(RC::RC_STATUS_TICKET_CHANGE_ERROR);
        }
        
        return F::corReturn(array('params' => array('status' => $this->status)));
    }
}