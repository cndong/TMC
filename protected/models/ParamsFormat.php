<?php
class ParamsFormat {
    const INT = '/^\d+$/';
    const INTZ = '/^0$/';
    const INTNZ = 'array("ParamsFormat", "isIntNotZero")';
    const FLOAT = '/^\d+(\.\d+)?$/';
    const FLOATZ = '/^0(\.0+)?$/';
    const FLOATNZ = 'array("ParamsFormat", "isFloatNotZero")';
    const TEXT = '/^.*$/';
    const TEXTZ = '/^$/';
    const TEXTNZ = '/^.+$/';
    const JSON = 'array("F", "isJson")';
    const ISARRAY = 'is_array';
    const ALNUM = 'ctype_alnum';
    const ALNUMX = '/^\w+$/';
    const ALNUMXX = '/^[0-9a-zA-Z_-]+$/';
    const BOOL = '/^[01]$/';
    const BOOLT = '1';
    const BOOLF = '0';
    const DATE = 'array("ParamsFormat", "isDate")';
    const TIME = 'array("ParamsFormat", "isTime")';
    const DATEHM = 'array("ParamsFormat", "isDateHM")';
    const TIMEHM = 'array("ParamsFormat", "isTimeHM")';
    const DATETIME = 'array("F", "isDateTime")';
    const TIMESTAMP = 'array("F", "isTimestamp")';
    const MOBILE = '/^1[3578]\d{9}$/';
    const MD5 = '/^\w{32}$/';
    const UNIQ_ID = '/^[A-Z0-9]{14}$/';
    const API_VERSION = '/^\d{1,2}\.\d{1,2}\.\d{1,2}$/';
    const U_ID = '/^\d{6,7}$/';
    const M_ID = 'array("Merchant", "isMerchantID")';
    const UNAME = '/^.{1,50}$/';
    const CARD_NO = '/^[a-zA-Z0-9]{5,18}$/';
    const CARD_NO_ID = '/^\d{6}(19|20)\d2[01][0-3]\d{4}[\d|x|X]$/';
    const ORDER_ID_U = '/^[A-Z0-9]{15}$/';
    const M_ORDER_ID = '/^\w{5,32}$/';
    const SORT_DIRECTION = 'array("ParamsFormat", "isSortDirection")';
    const F_CN_CITY_CODE = 'array("DictFlight", "isCNCityCode")';
    const F_CN_AIRPORT_CODE = 'array("DictFlight", "isCNAirportCode")';
    const F_AIRLINE_TCODE = '/^[A-Z]{2}$/';
    const F_CRAFT_CODE = '/^[A-Z0-9]{3}$/';
    const F_CRAFT_TYPE = 'array("DictFlight", "isCraftType")';
    const F_FLIGHT_NO = '/^[A-Z0-9]{5,6}$/';
    const F_CABIN_CODE = '/^[A-Z]\d?$/';
    const F_CABIN_CLASS = 'array("DictFlight", "isCabinClass")';
    const F_STATUS = 'array("FlightStatus", "isFlightStatus")';
    const F_STATUS_ARRAY = 'array("FlightStatus", "isFlightStatusArray")';
    const F_PNR = '/^[A-Z0-9]{6}$/';
    const F_TICKET_NO = '/^\d{3}-\d{10}$/';
    const F_TERM = '/^(--)|(T\d)$/';
    const PASSENGER_TYPE = 'array("Dict", "isPassengerType")';
    const CARD_TYPE = 'array("Dict", "isCardType")';
    const SEX = 'array("Dict", "isSex")';
    const SMS_CODE = '/^\d{6}$/';
    const SMS_SIGN = 'array("SMS", "isSMSSign")';
    const HOTEL_ID = '/^[0-9]{4}$/';
    const HOTEL_STAR = '/^[0-9]{6}$/';
    
    public static function isDate($str) {
        return F::isDateTime($str, 'Y-m-d');
    }
    
    public static function isTime($str) {
        return F::isDateTime($str, 'H:i:s');
    }
    
    public static function isTimeHM($str) {
        return F::isDateTime($str, 'H:i');
    }
    
    public static function isDateHM($str) {
        return F::isDateTime($str, 'Y-m-d H:i');
    }
    
    public static function isIntNotZero($str) {
        return ctype_digit(strval($str)) && intval($str) > 0;
    }
    
    public static function isFloatNotZero($str) {
        return is_numeric($str) && floatval($str) > 0;
    }
    
    public static function isSortDirection($str) {
        return in_array(strtolower($str), array('desc', 'asc'));
    }
}