<?php
class Dict {
    const OPERATER_SYSTEM = 1;
    
    const STATUS_TRUE = 1;
    const STATUS_FALSE = 0;
    
    const BUSINESS_FLIGHT = 1;
    const BUSINESS_TRAIN = 2;
    const BUSINESS_BUS = 3;
    const BUSINESS_HOTEL = 4;
    public static $businesses = array(
        self::BUSINESS_FLIGHT => array('name' => '飞机票', 'str' => 'flight'),
        self::BUSINESS_TRAIN => array('name' => '火车票', 'str' => 'train'),
        self::BUSINESS_BUS => array('name' => '汽车票', 'str' => 'bus'),
        self::BUSINESS_HOTEL => array('name' => '酒店', 'str' => 'hotel'),
    );
    
    public static function isBusinessID($businessID) {
        return isset(self::$businesses[$businessID]);
    }
    
    const TRIP_TYPE_ONEWAY = 1;
    const TRIP_TYPE_ROUND = 2;
    public static $tripTypes = array(
        self::TRIP_TYPE_ONEWAY => array('name' => '单程'),
        self::TRIP_TYPE_ROUND => array('name' => '往返')
    );
    
    const SEX_MALE = 0;
    const SEX_FEMALE = 1;
    public static $sexTypes = array(
        self::SEX_MALE => array('name' => '男'),
        self::SEX_FEMALE => array('name' => '女')
    );
    
    public static function isSex($sexType) {
        return isset(self::$sexTypes[$sexType]);
    }
    
    const PASSENGER_TYPE_ADULT = 1;
    const PASSENGER_TYPE_CHILD = 2;
    const PASSENGER_TYPE_BABY = 3;
    const PASSENGER_TYPE_STUDENT = 4;
    const PASSENGER_TYPE_DISABLE = 5;
    public static $passengerTypes = array(
        self::PASSENGER_TYPE_ADULT => array(
            'name' => '成人票',
            'public' => array(self::BUSINESS_FLIGHT, self::BUSINESS_TRAIN, self::BUSINESS_BUS),
            'business' => array(self::BUSINESS_FLIGHT => array(), self::BUSINESS_TRAIN => array(), self::BUSINESS_BUS => array())
        ),
        self::PASSENGER_TYPE_CHILD => array(
            'name' => '儿童票',
            'business' => array(self::BUSINESS_FLIGHT => array(), self::BUSINESS_TRAIN => array()),
        ),
        self::PASSENGER_TYPE_BABY => array(
            'name' => '婴儿票',
            'business' => array(self::BUSINESS_FLIGHT => array())
        ),
        self::PASSENGER_TYPE_STUDENT => array(
            'name' => '学生票',
            'business' => array(self::BUSINESS_TRAIN => array())
        ),
        self::PASSENGER_TYPE_DISABLE => array(
            'name' => '残军票',
            'business' => array(self::BUSINESS_TRAIN => array())
        )
    );
    
    public static function getPassengerTypesByBusiness($business) {
        $rtn = array();
        foreach (self::$passengerTypes as $passengerType => $config) {
            if (isset($config['business'][$business])) {
                $rtn[$passengerType] = $config['business'][$business];
            }
        }
        
        return $rtn;
    }
    
    public static function getPassengerPublic($passengerType) {
        return !empty(self::$passengerTypes[$passengerType]) ? self::$passengerTypes[$passengerType] : array();
    }
    
    public static function isPassengerType($passengerType) {
        return isset(self::$passengerTypes[$passengerType]);
    }
    
    const CARD_TYPE_SF = 1;
    const CARD_TYPE_GA = 2;
    const CARD_TYPE_TW = 3;
    const CARD_TYPE_HZ = 4;
    public static $cardTypes = array(
        self::CARD_TYPE_SF => array(
            'name' => '二代身份证',
            'business' => array(self::BUSINESS_FLIGHT => array(), self::BUSINESS_TRAIN => array(), self::BUSINESS_BUS => array())
        ),
        self::CARD_TYPE_GA => array(
            'name' => '港澳通行证',
            'business' => array(self::BUSINESS_FLIGHT => array(), self::BUSINESS_TRAIN => array())
        ),
        self::CARD_TYPE_TW => array(
            'name' => '台湾通行证',
            'business' => array(self::BUSINESS_FLIGHT => array(), self::BUSINESS_TRAIN => array())
        ),
        self::CARD_TYPE_HZ => array(
            'name' => '护照',
            'business' => array(self::BUSINESS_FLIGHT => array(), self::BUSINESS_TRAIN => array())
        )
    );
    
    public static function getCardTypesByBusiness($business) {
        $rtn = array();
        foreach (self::$cardTypes as $cardType => $config) {
            if (isset($config['business'][$business])) {
                $rtn[$cardType] = $config['business'][$business];
            }
        }
        
        return $rtn;
    }
    
    public static function isCardType($cardType) {
        return isset(self::$cardTypes[$cardType]);
    }
    
    const INVOICE_PRICE = 2000;
}