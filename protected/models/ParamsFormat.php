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
    const U_ID = '/^\d{6,7}$/';
    const M_ID = 'array("Merchant", "isMerchantID")';
    const UNAME = '/^.{1,50}$/';
    const CARD_NO = '/^[a-zA-Z0-9]{5,18}$/';
    const CARD_NO_ID = '/^\d{6}(19|20)\d2[01][0-3]\d{4}[\d|x|X]$/';
    const ORDER_ID_U = '/^[A-Z0-9]{15}$/';
    const M_ORDER_ID = '/^\w{5,32}$/';
    const T_TRAIN_NO = '/^[A-Z]?\d{1,4}$/';
    const T_STATION_CODE = '/^[A-Z]{3}$/';
    const T_SEAT_TYPE = 'array("DictTrain", "isSeatType")';
    const T_PASSENGER_TYPE = 'array("DictTrain", "isPassengerType")';
    const T_CARD_TYPE = 'array("DictTrain", "isCardType")';
    const T_CARD_REAL_TYPE = 'array("DictTrain", "isRealCardType")';
    const T_JOURNEY_TYPE = 'array("DictTrain", "isJourneyType")';
    const T_JOURNEY_REAL_TYPE = 'array("DictTrain", "isRealJourneyType")';
    const T_INSURE_TYPE = 'array("DictTrain", "isInsureType")';
    const T_TICKET_NO = '/^E[A-Z0-9]{9}$/';
    const T_ORDER_STATUS_ARRAY = 'array("TrainStatus", "isOrderStatusArray")';
    const SORT_DIRECTION = 'array("ParamsFormat", "isSortDirection")';
    const F_CN_CITY_CODE = 'array("DictFlight", "isCNCityCode")';
    const F_CN_AIRPORT_CODE = 'array("DictFlight", "isCNAirportCode")';
    const F_AIRLINE_TCODE = '/^[A-Z]{2}$/';
    const F_CRAFT_CODE = '/^[A-Z0-9]{3}$/';
    const F_FLIGHT_NO = '/^[A-Z0-9]{5,6}$/';
    const F_CABIN_CODE = '/^[A-Z]$/';
    const F_CABIN_CLASS = '/^[A-Z]$/';
    const PASSENGER_TYPE = 'array("Dict", "isPassengerType")';
    const CARD_TYPE = 'array("Dict", "isCardType")';
    const SEX = 'array("Dict", "isSex")';
    
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