<?php
class FlightCNOrder extends QActiveRecord {
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return '{{flightCNOrder}}';
    }

    public function rules() {
        return array(
            array('merchantID, userID, departmentID, companyID, contacterID, isPrivate, isInsured, isInvoice, isRound, segmentNum, passengerIDs, passengerNum, orderPrice, ticketPrice, airportTaxPrice, oilTaxPrice', 'required'),
            array('merchantID, userID, departmentID, companyID, contacterID, isPrivate, isInsured, isInvoice, isRound, segmentNum, passengerNum, invoicePrice, invoiceAddressID, invoicePostID, operaterID, status, ctime, utime', 'numerical', 'integerOnly' => True),
            array('orderPrice, payPrice, ticketPrice, airportTaxPrice, oilTaxPrice, insurePrice, invoicePostPrice', 'numerical'),
            array('passengerIDs', 'length', 'max' => 60),
            array('invoiceTradeNo, tradeNo', 'length', 'max' => 32),
            array('id, merchantID, userID, departmentID, companyID, contacterID, isPrivate, isInsured, isInvoice, isRound, segmentNum, passengerIDs, passengerNum, orderPrice, payPrice, ticketPrice, airportTaxPrice, oilTaxPrice, insurePrice, invoicePrice, invoiceAddressID, invoicePostPrice, invoicePostID, invoiceTradeNo, tradeNo, operaterID, status, ctime, utime', 'safe', 'on'=>'search'),
        );
    }
    
    public function relations() {
        return array(
            'contacter' => array(self::BELONGS_TO, 'UserContacter', 'contacterID'),
            'address' => array(self::BELONGS_TO, 'UserAddress', 'invoiceAddressID'),
            'segments' => array(self::HAS_MANY, 'FlightCNSegment', 'orderID')
        );
    }
    
    public function getPassengers() {
        $criteria = new CDbCriteria();
        $criteria->addInCondition('id', explode('-', $this->passengerIDs));
        $passengers = UserPassenger::model()->findAll($criteria);
    
        $rtn = array();
        foreach ($passengers as $passenger) {
            $rtn[UserPassenger::getPassengerKey($passenger)] = F::arrayGetByKeys($passenger, array('id', 'name' , 'type', 'cardType', 'cardNo', 'birthday', 'sex'));
        }
    
        return $rtn;
    }
    
    public static function classifyPassengers($passengers) {
        $rtn = array_fill_keys(array_keys(DictFlight::$ticketTypes), array());
        foreach ($passengers as $passenger) {
            $passenger = F::arrayGetByKeys($passenger, array('name', 'type', 'cardType', 'cardNo', 'birthday', 'sex'));
            $rtn[$passenger['type']][UserPassenger::getPassengerKey($passenger)] = $passenger;
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
            'invoiceAddress' => '!' . ParamsFormat::ISARRAY . '--'
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
        } else {
            if ($contacter = UserContacter::model()->findByAttributes($tmp, 'deleted=:deleted', array(':deleted' => UserContacter::DELETED_F))) {
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
            } else {
                if ($address = UserAddress::model()->findByAttributes($tmp, 'deleted=:deleted', array(':deleted' => UserAddress::DELETED_F))) {
                    $tmp = array('addressID' => $address->id);
                }
            }
            $params['invoiceAddress'] = $tmp;
        }

        if (($params['passengerNum'] = count($params['passengers'])) > DictFlight::MAX_PASSENGER_NUM) {
            return F::errReturn(RC::RC_F_PASSENGER_NUM_ERROR);
        }
    
        //检测常用乘客人
        $passengers = array(); //用于统计价格
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
                    $tmp = array('passengerID' => $tmpPassenger->id);
                }
            }
            $passengers[] = $passenger;
            $params['passengers'][$k] = $tmp;
        }
        $passengers = self::classifyPassengers($passengers);
    
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
    
                if (is_numeric($realCabin['cabinNum']) && $realCabin['cabinNum'] < count($params['passengers'])) {
                    return F::errReturn(RC::RC_F_CABIN_NUM_ERROR);
                }
    
                $modifySegment = &$params[$routeType]['segments'][$segmentIndex];
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
                $params['contacter'] = array('contacterID' => $res['data']->id);
            }
    
            if ($params['isInvoice'] && !isset($params['invoiceAddress']['addressID'])) {
                if (!F::isCorrect($res = UserAddress::createAddress($params['invoiceAddress']))) {
                    throw new Exception($res['rc']);
                }
                $params['invoiceAddress'] = array('addressID' => $res['data']->id);
            } else {
                $params['invoiceAddress'] = array('addressID' => 0);
            }
    
            foreach ($params['passengers'] as $index => $passenger) {
                if (!isset($passenger['passengerID'])) {
                    if (!F::isCorrect($res = UserPassenger::createPassenger($passenger))) {
                        throw new Exception($res['rc']);
                    }
                    $params['passengers'][$index] = array('passengerID' => $res['data']->id);
                }
            }
    
            $attributes = F::arrayGetByKeys($params, array('merchantID', 'userID', 'departmentID', 'companyID', 'isPrivate', 'isInsured', 'isInvoice', 'isRound', 'segmentNum', 'passengerNum'));
            $attributes = array_merge($attributes, $params['price']);
            $attributes['contacterID'] = $params['contacter']['contacterID'];
            $attributes['invoiceAddressID'] = $params['invoiceAddress']['addressID'];
            $attributes['passengerIDs'] = implode('-', F::arrayGetField($params['passengers'], 'passengerID', True));
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
                        'departTime', 'arriveTime', 'airlineCode', 'craftCode', 'craftType', 'adultAirportTax', 'adultOilTax',
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
            return F::corReturn($order);
        } catch (Exception $e) {
            $train->rollback();
            return F::errReturn($e->getMessage());
        }
    }
}