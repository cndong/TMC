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
            array('merchantID, userID, contacterName, contacterMobile, isPrivate, isRound, isInsured, isInvoice, flightNo, airlineCode, craftCode, cabin, cabinClass, departCityCode, arriveCityCode, departAirportCode, arriveAirportCode, departTime, arriveTime, passengers, passengerNum, adultPrice, childPrice, babyPrice, adultAirportTax, childAirportTax, babyAirportTax, adultOilTax, childOilTax, babyOilTax, orderPrice, payPrice, taoPrice', 'required'),
            array('merchantID, userID, isPrivate, isRound, isInsured, isInvoice, departTime, arriveTime, passengerNum, invoicePrice, invoicePostID, operaterID, bookOperaterID, payOperaterID, status, ctime, utime', 'numerical', 'integerOnly'=>true),
            array('adultPrice, childPrice, babyPrice, adultAirportTax, childAirportTax, babyAirportTax, adultOilTax, childOilTax, babyOilTax, orderPrice, payPrice, taoPrice, insurePrice, invoicePostPrice, refundPrice', 'numerical'),
            array('contacterName', 'length', 'max'=>50),
            array('contacterMobile', 'length', 'max'=>11),
            array('flightNo', 'length', 'max'=>6),
            array('airlineCode', 'length', 'max'=>2),
            array('craftCode, departCityCode, arriveCityCode, departAirportCode, arriveAirportCode', 'length', 'max'=>3),
            array('cabin, cabinClass', 'length', 'max'=>1),
            array('passengers', 'length', 'max'=>600),
            array('invoiceAddress', 'length', 'max'=>255),
            array('tradeNo, invoiceTradeNo', 'length', 'max'=>32),
            array('id, merchantID, userID, contacterName, contacterMobile, isPrivate, isRound, isInsured, isInvoice, flightNo, airlineCode, craftCode, cabin, cabinClass, departCityCode, arriveCityCode, departAirportCode, arriveAirportCode, departTime, arriveTime, passengers, passengerNum, adultPrice, childPrice, babyPrice, adultAirportTax, childAirportTax, babyAirportTax, adultOilTax, childOilTax, babyOilTax, orderPrice, payPrice, taoPrice, insurePrice, invoicePrice, invoiceAddress, invoicePostID, invoicePostPrice, tradeNo, invoiceTradeNo, refundPrice, operaterID, bookOperaterID, payOperaterID, status, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public static function classifyPassengers($passengers) {
        $rtn = array_fill_keys(array_keys(DictFlight::$ticketTypes), array());
        foreach ($passengers as $passenger) {
            if (is_object($passenger)) {
                $passenger = F::arrayGetByKeys($passenger, array('name', 'type', 'cardType', 'cardNo', 'birthday', 'sex'));
            }
            $rtn[$passenger['type']] = $passenger;
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
        
        //检测往返航程、航段 array('routeKey' => 'ax8ands', 'segments' => array(array('F', 'orderParams')));
        $totalTicketPrice = $totalInsurePrice = $segmentNum = 0;
        $routeTypes = empty($params['isRound']) ? array('departRoute') : array('departRoute', 'returnRoute');
        foreach ($routeTypes as $routeType) {
            if (!($tmp = F::checkParams($params[$routeType], array(
                'departCityCode' => ParamsFormat::F_CN_CITY_CODE, 'arriveCityCode' => ParamsFormat::F_CN_CITY_CODE,
                'departDate' => ParamsFormat::DATE, 'routeKey' => ParamsFormat::MD5, 'segments' => ParamsFormat::ISARRAY
            )))) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            
            $routesParams = F::arrayGetByKeys($tmp, array('departCityCode', 'arriveCityCode', 'departDate'));
            if (!F::isCorrect($res = ProviderF::getCNFlightDetail($routesParams, True))) {
                return $res;
            }
            
            //检测有无此航程
            if (empty($res['data']['routes'][$tmp['routeKey']])) {
                return F::errReturn(RC::RC_F_ROUTE_NOT_EXISTS);
            }
            
            //检测此航程的航段信息是否正确
            foreach ($tmp['segments'] as $segmentIndex => $segment) {
                $segmentNum++;
                if (count($segment) != 2) {
                    return F::errReturn(RC::RC_F_SEGMENT_ERROR);
                }
                list($cabin, $flight) = $segment;
                $flight = ProviderF::decryptOrderParams($flight);
                
                $segmentParams = F::arrayGetByKeys($flight, array('departCityCode', 'arriveCityCode', 'flightNo'));
                $segmentParams = date('Y-m-d', $flight['departTime']);
                if (!F::isCorrect($res = ProviderF::getCNFlightList($segmentParams, True))) {
                    return $res;
                }
                
                $flightKey = ProviderF::getFlightKey($flight['departCityCode'], $flight['arriveCityCode'], $flight['flightNo']);
                if (empty($res['data']['flights'][$flightKey]) || ProviderF::decryptOrderParams($res['data']['flights'][$flightKey]['orderParams']) != $flight) {
                    return F::errReturn(RC::RC_F_INFO_CHANGED);
                }
                
                if (!isset($flight['cabins'][$cabin])) {
                    return F::errReturn(RC::RC_F_NO_SUCH_CABIN);
                }
                
                $params[$routeType]['segments'][$segmentIndex] = $flight;
                foreach (DictFlight::$ticketTypes as $ticketType => $ticketTypeConfig) {
                    $totalTicketPrice += count($passengers[$ticketType]) * $flight['cabins'][$cabin][$ticketTypeConfig['str'] . 'Price'];
                    $totalInsurePrice += intval($params['isInsured']) * DictFlight::INSURE_PRICE * count($passengers[$ticketType]);
                }
            }
        }
        $params['segmentNum'] = $segmentNum;
        
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
                return F::errReturn(RC::RC_F_PRICE_ERROR);
            }
        }
        
        return F::corReturn($params);
    }
    
    public static function createOrder($params) {
        if (!F::isCorrect($res = self::_checkCreateOrderParams($params))) {
            return $res;
        }
        $batchNo = $params['segmentNum'] > 1 ? Q::getUniqueID() : '';
        
        array(
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
}