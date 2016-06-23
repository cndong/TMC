<?php
class TrainOrder extends QActiveRecord {
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
            array('contactName', 'length', 'max' => 50),
            array('contactMobile', 'length', 'max' => 11),
            array('passengers', 'length', 'max' => 655),
            array('invoiceAddress, reason', 'length', 'max' => 500),
            array('invoiceTradeNo, tradeNo', 'length', 'max' => 32),
            array('id, merchantID, userID, departmentID, companyID, contactName, contactMobile, reviewerID, isPrivate, isInsured, isInvoice, isRound, passengers, passengerNum, orderPrice, payPrice, ticketPrice, insurePrice, invoicePrice, invoiceAddress, invoicePostPrice, invoicePostID, invoiceTradeNo, tradeNo, reason, operaterID, status, ctime, utime', 'safe', 'on' => 'search'),
        );
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
                $attributes = $tmp;
                $attributes['trainType'] = $attributes['type'];
                unset($attributes['type'], $attributes['businessID']);
                if ($tmpPassenger = UserPassenger::model()->findByAttributes($attributes, 'deleted=:deleted', array(':deleted' => UserPassenger::DELETED_F))) {
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
                    if (!F::isCorrect($res = UserPassenger::createPassenger($passenger))) {
                        throw new Exception($res['rc']);
                    }
                    $passenger = $res['data'];
                }
                $passengers[] = $passenger;
            }
        
            $attributes = F::arrayGetByKeys($params, array('merchantID', 'userID', 'departmentID', 'companyID', 'isPrivate', 'isInsured', 'isInvoice', 'isRound', 'passengerNum', 'reason'));
            $attributes = array_merge($attributes, $params['price']);
            $attributes['contactName'] = $params['contacterObj']->name;
            $attributes['contactMobile'] = $params['contacterObj']->mobile;
            $attributes['invoiceAddress'] = !$params['isInvoice'] ? '' : $params['invoiceAddressObj']->getDescription();
            $attributes['passengers'] = UserPassenger::concatPassengers($passengers, Dict::BUSINESS_TRAIN);
            $attributes['status'] = $params['isPrivate'] ? FlightStatus::WAIT_PAY : FlightStatus::WAIT_CHECK;
        
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
}