<?php
class FlightCNOrder extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{flightCNOrder}}';
    }

    public function rules() {
        return array(
            array('merchantID, userID, contacterID, isPrivate, isRound, isInsured, isInvoice, flightNo, airlineCode, craftCode, cabin, cabinClass, departCityCode, arriveCityCode, departAirportCode, arriveAirportCode, departTime, arriveTime, passengerIDs, passengerNum, adultPrice, childPrice, babyPrice, adultAirportTax, childAirportTax, babyAirportTax, adultOilTax, childOilTax, babyOilTax, orderPrice, payPrice, taoPrice, batchNo', 'required'),
            array('merchantID, userID, contacterID, isPrivate, isRound, isInsured, isInvoice, departTime, arriveTime, passengerNum, invoicePrice, invoicePostID, batchNo, operaterID, status, ctime, utime', 'numerical', 'integerOnly' => True),
            array('adultPrice, childPrice, babyPrice, adultAirportTax, childAirportTax, babyAirportTax, adultOilTax, childOilTax, babyOilTax, orderPrice, payPrice, taoPrice, insurePrice, invoicePostPrice', 'numerical'),
            array('flightNo', 'length', 'max' => 6),
            array('airlineCode', 'length', 'max' => 2),
            array('craftCode, departCityCode, arriveCityCode, departAirportCode, arriveAirportCode', 'length', 'max' => 3),
            array('cabin, cabinClass', 'length', 'max' => 2),
            array('passengerIDs', 'length', 'max' => 60),
            array('invoiceAddress', 'length', 'max' => 255),
            array('tradeNo, invoiceTradeNo', 'length', 'max' => 32),
            array('batchNo' => ''),
            array('id, merchantID, userID, contacterID, isPrivate, isRound, isInsured, isInvoice, flightNo, airlineCode, craftCode, cabin, cabinClass, departCityCode, arriveCityCode, departAirportCode, arriveAirportCode, departTime, arriveTime, passengers, passengerNum, adultPrice, childPrice, babyPrice, adultAirportTax, childAirportTax, babyAirportTax, adultOilTax, childOilTax, babyOilTax, orderPrice, payPrice, taoPrice, insurePrice, invoicePrice, invoiceAddress, invoicePostID, invoicePostPrice, tradeNo, invoiceTradeNo, operaterID, status, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public static function getPassengerKey($passenger) {
        $rtn = array();
        foreach (array('name', 'cardNo', 'type') as $k) {
            $rtn[] = is_object($passenger) ? $passenger->$k : $passenger[$k];
        }
        
        return implode('_', $rtn);
    }
    
    public static function classifyPassengers($passengers) {
        $rtn = array_fill_keys(array_keys(DictFlight::$ticketTypes), array());
        foreach ($passengers as $passenger) {
            if (is_object($passenger)) {
                $passenger = F::arrayGetByKeys($passenger, array('name', 'type', 'cardType', 'cardNo', 'birthday', 'sex'));
            }
            $rtn[$passenger['type']][self::getPassengerKey($passenger)] = $passenger;
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
    
    private static function _getOrderParams() {
        return array(
            'merchantID' => 1,
            'userID' => 1,
            'isPrivate' => 0,
            'isInsured' => 0,
            'isInvoice' => 0,
            'isRound' => 0,
            'contacter' => array(
                'name' => '随永杰',
                'mobile' => '13141353663'
            ),
            'passengers' => array(
                array(
                    'name' => '随永杰',
                    'type' => Dict::PASSENGER_TYPE_ADULT,
                    'cardType' => Dict::CARD_TYPE_SF,
                    'cardNo' => '130534198902094912',
                    'birthday' => '1989-02-09',
                    'sex' => Dict::SEX_MALE
                )
            ),
            'price' => array(
                'orderPrice' => 67000,
                'ticketPrice' => 62000,
                'airportTaxPrice' => 5000,
                'oilTaxPrice' => 0,
                'insurePrice' => 0,
                'invoicePrice' => 0
            ),
            'departRoute' => array(
                'departCityCode' => 'BJS',
                'arriveCityCode' => 'SHA',
                'departDate' => '2016-06-22',
                'routeKey' => '0415319315b6c684b3e0c3c097bd7b49',
                'segments' => array(
                    array(
                        'flightNo' => 'CZ9271',
                        'departCityCode' => 'BJS',
                        'arriveCityCode' => 'SHA',
                        'departAirportCode' => 'PEK',
                        'arriveAirportCode' => 'SHA',
                        'departTime' => 1466550000,
                        'arriveTime' => 1466558100,
                        'airlineCode' => 'CZ',
                        'craftCode' => '333',
                        'cabinInfo' => array(
                            'cabin' => 'E',
                            'cabinClass' => 3,
                            'adultPrice' => 62000,
                            'childPrice' => 62000,
                            'babyPrice' => 12400,
                        ),
                        'adultAirportTax' => 5000,
                        'adultOilTax' => 0,
                        'childAirportTax' => 0,
                        'childOilTax' => 0,
                        'babyAirportTax' => 0,
                        'babyOilTax' => 0
                    ),
                    
                )
            ),
        );
    }
    
    private static function _checkCreateOrderParams($params) {
        if (!$params = F::checkParams($params, self::_getCreateOrderFormats())) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        //检测用户
        if (!User::model()->findByPk($params['userID'])) {
            return F::errReturn(RC::RC_USER_NOT_EXISTS);
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
            }
            $params['invoiceAddress'] = $tmp;
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
            }
            $passengers[] = $passenger;
            $params['passengers'][$k] = $tmp;
        }
        $passengers = self::classifyPassengers($passengers);
        
        //检测往返航程、航段 array('routeKey' => 'ax8ands', 'segments' => array(array(...)));
        $totalTicketPrice = $totalAirportTaxPrice = $totalOilTaxPrice = $totalInsurePrice = $segmentNum = 0;
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
                $segmentNum++;
                
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
                
                $modifySegment = &$params[$routeType]['segments'][$segmentIndex];
                $modifySegment['craftType'] = DictFlight::CRAFT_LARGE;
                foreach ($realCarftMap as $craftTypeStr => $craftCodes) {
                    if (in_array($segment['craftCode'], $craftCodes)) {
                        $modifySegment['craftType'] = DictFlight::getCraftTypeByStr($craftTypeStr);
                        break;
                    }
                }
                $modifySegment = CMap::mergeArray($modifySegment, array_fill_keys(array('orderPrice', 'ticketPrice', 'airportTaxPrice', 'oilTaxPrice', 'insurePrice', 'invoicePrice'), 0));
                foreach (DictFlight::$ticketTypes as $ticketType => $ticketTypeConfig) {
                    $modifySegment['ticketPrice'] += count($passengers[$ticketType]) * $segment['cabinInfo'][$ticketTypeConfig['str'] . 'Price'];
                    $modifySegment['insurePrice'] += intval($params['isInsured']) * DictFlight::INSURE_PRICE * count($passengers[$ticketType]);
                    $modifySegment['airportTaxPrice'] += count($passengers[$ticketType]) * $segment[$ticketTypeConfig['str'] . 'AirportTax'];
                    $modifySegment['oilTaxPrice'] += count($passengers[$ticketType]) * $segment[$ticketTypeConfig['str'] . 'OilTax'];
                }
                $totalTicketPrice += $modifySegment['ticketPrice'];
                $totalInsurePrice += $modifySegment['insurePrice'];
                $totalAirportTaxPrice += $modifySegment['airportTaxPrice'];
                $totalOilTaxPrice += $modifySegment['oilTaxPrice'];
            }
        }
        $params['batchNo'] = $segmentNum > 1 ? Q::getUniqueID() : '';
        
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
        $params = self::_getOrderParams();
        if (!F::isCorrect($res = self::_checkCreateOrderParams($params))) {
            return $res;
        }
        $params = $res['data'];
        
        $records = array();
        $attributes = F::arrayGetByKeys($params, array('merchantID', 'userID', 'isPrivate', 'isInsured', 'isInvoice', 'isRound', 'batchNo'));
        $routeTypes = empty($params['isRound']) ? array('departRoute') : array('departRoute', 'returnRoute');
        foreach ($routeTypes as $routeType) {
            foreach ($params[$routeType]['segments'] as $segmentIndex => $segment) {
                $record = CMap::mergeArray($attributes, F::arrayGetByKeys($segment, array(
                    'flightNo', 'departCityCode', 'arriveCityCode', 'departTime', 'arriveTime', 'airlineCode', 'craftCode', 'craftType',
                    'adultAirportTax', 'adultOilTax', 'childAirportTax', 'childOilTax', 'babyAirportTax', 'babyAirportTax',
                    'ticketPrice', 'insurePrice', 'airportTaxPrice', 'oilTaxPrice'
                )));
                $record = CMap::mergeArray($record, $segment['cabinInfo']);
            }
            
            $records[] = $record;
        }
        
        try {
            if (!isset($params['contacter']['contacterID'])) {
                if ($contacter = UserContacter::model()->findByAttributes($params['contacter'], 'deleted=:deleted', array(':deleted' => UserContacter::DELETED_F))) {
                    
                }
            }
        }
        //'passengerIDs', 'passengerNum', 'payPrice', 'taoPrice', 
        
        //需要处理联系人、乘客、地址自动添加
        var_dump($records);exit;
    }
}