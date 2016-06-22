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
        
        $routeTypes = empty($params['isRound']) ? array('departRoute') : array('departRoute', 'returnRoute');
        foreach ($routeTypes as $routeType) {
            if (!($tmp = F::checkParams($params[$routeType], array(
                'departStationCode' => ParamsFormat::T_STATION_CODE, 'arriveStationCode' => ParamsFormat::T_STATION_CODE,
                'departDate' => ParamsFormat::DATE, 'trainNo' => ParamsFormat::T_TRAIN_NO, 'seatType' => ParamsFormat::INTNZ,
                'seatPrice' => ParamsFormat::INTNZ
            )))) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            $routesParams = F::arrayGetByKeys($tmp, array('departStationCode', 'arriveStationCode', 'departDate'));
            if (!F::isCorrect($res = ProviderT::getTrainList($routesParams, True))) {
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
        
        
    }
}