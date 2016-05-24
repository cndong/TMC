<?php
class DictFlight {
    const FLIGHT_TYPE_CN = 1;
    const FLIGHT_TYPE_IN = 2;
    public static $flightTypes = array(
        self::FLIGHT_TYPE_CN => array('name' => '国际', 'str' => 'cn'),
        self::FLIGHT_TYPE_IN => array('name' => '国内', 'str' => 'in'),
    );
    
    const TICKET_TYPE_ADULT = 1;
    const TICKET_TYPE_CHILD = 2;
    const TICKET_TYPE_BABY = 3;
    public static $ticketTypes = array(
        self::TICKET_TYPE_ADULT => array('name' => '成人票', 'ext' => '', 'str' => 'adult'),
        self::TICKET_TYPE_CHILD => array('name' => '儿童票', 'ext' => '2-12周岁', 'str' => 'child'),
        self::TICKET_TYPE_BABY => array('name' => '婴儿票', 'ext' => '14天-2周岁', 'str' => 'baby')
    );
    
    const CERTIFICATE_TYPE_SFZ = 1;
    const CERTIFICATE_TYPE_HZ = 2;
    const CERTIFICATE_TYPE_JRZ = 3;
    const CERTIFICATE_TYPE_HXZ = 4;
    const CERTIFICATE_TYPE_TWTXZ = 5;
    const CERTIFICATE_TYPE_GATXZ = 6;
    const CERTIFICATE_TYPE_HKB = 7;
    const CERTIFICATE_TYPE_CSZM = 8;
    const CERTIFICATE_TYPE_QT = 99;
    public static $certificateTypes = array(
        self::CERTIFICATE_TYPE_SFZ => array('name' => '身份证'),
        self::CERTIFICATE_TYPE_HZ => array('name' => '护照'),
        self::CERTIFICATE_TYPE_JRZ => array('name' => '军人证'),
        self::CERTIFICATE_TYPE_HXZ => array('name' => '回乡证'),
        self::CERTIFICATE_TYPE_TWTXZ => array('name' => '台湾通行证'),
        self::CERTIFICATE_TYPE_GATXZ => array('name' => '港澳通行证'),
        self::CERTIFICATE_TYPE_HKB => array('name' => '户口簿'),
        self::CERTIFICATE_TYPE_CSZM => array('name' => '出生证明'),
        self::CERTIFICATE_TYPE_QT => array('name' => '其他')
    );
    
    const CABIN_TD = 1;
    const CABIN_SW = 2;
    const CABIN_JJ = 3;
    public static $cabinClasses = array(
        self::CABIN_TD => array('name' => '头等舱'),
        self::CABIN_SW => array('name' => '商务舱'),
        self::CABIN_JJ => array('name' => '经济舱'),
    );
    
    const CRAFT_LARGE = 1;
    const CRAFT_MIDDLE = 2;
    const CRAFT_SMALL = 3;
    public static $craftTypes = array(
        self::CRAFT_LARGE => array('name' => '大', 'str' => 'large'),
        self::CRAFT_MIDDLE => array('name' => '中', 'str' => 'middle'),
        self::CRAFT_SMALL => array('name' => '小', 'str' => 'small'),
    );
    
    public static function getCraftTypeByStr($str) {
        foreach (self::$craftTypes as $craftType => $config) {
            if ($config['str'] == $str) {
                return $craftType;
            }
        }
        
        return self::CRAFT_LARGE;
    }
    
    const RATE_CHILD = 0.5;
    const RATE_BABY = 0.1;
    
    const INSURE_PRICE = 30;
    
    public static function isCNCityCode($cityCode) {
        $cities = ProviderF::getCNCityList();
        return isset($cities[$cityCode]);
    }
    
    public static function isCNAirportCode($airportCode) {
        $airports = ProviderF::getCNAirportList();
        return isset($airports[$airportCode]);
    }
    
    public static function isINCityCode($cityCode) {
        $cities = ProviderF::getINCityList();
        return isset($cities[$cityCode]);
    }
    
    public static function isINAirportCode($airportCode) {
        $airports = ProviderF::getINAirportList();
        return isset($airports[$airportCode]);
    }
}