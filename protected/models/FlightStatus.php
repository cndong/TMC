<?php
class FlightStatus {
    const WAIT_CHECK = 1;
    const CHECK_FAIL = 2;
    const CHECK_SUCC = 3;
    const WAIT_PAY = 4; //个人票使用
    const PAYED = 5; //个人票使用
    const CANCELD = 6;
    const BOOKING = 7;
    const BOOK_FAIL = 8;
    const BOOK_SUCC = 9;
    const BOOK_FAIL_RFDING = 10; //个人票使用
    const BOOK_FAIL_RFDED = 11; //个人票使用
    const APPLY_RSN = 12;
    const APPLY_RSNING = 13;
    const APPLY_RSD_FAIL = 14;
    const APPLY_RSD_SUCC = 15;
    const APPLY_RFD = 16;
    const APPLY_RFDING = 17;
    const APPLY_RFD_FAIL = 18;
    const APPLY_RFD_SUCC = 19;
    
    public static $flightStatus = array(
        self::WAIT_CHECK => array(
            'des' => array('user' => '待审批'),
            'str' => 'WaitCheck',
            'userStatus' => array(self::CHECK_FAIL, self::CHECK_SUCC, self::CANCELD)
        ),
        self::CHECK_FAIL => array(
            'des' => array('user' => '审批失败'),
            'str' => 'CheckFail',
        ),
        self::CHECK_SUCC => array(
            'des' => array('user' => '已审批'),
            'str' => 'CheckSucc',
            'userStatus' => array(self::CANCELD),
            'adminHdStatus' => array(self::BOOKING),
        ),
        self::WAIT_PAY => array(
            'des' => array('user' => '待支付'),
            'str' => 'WaitPay',
            'userStatus' => array(self::PAYED)
        ),
        self::PAYED => array(
            'des' => array('user' => '已支付'),
            'str' => 'Payed',
            'adminHdStatus' => array(self::BOOKING)
        ),
        self::BOOKING => array(
            'des' => array('user' => '正在出票'),
            'str' => 'Booking',
            'adminOpStatus' => array(self::BOOK_FAIL, self::BOOK_SUCC)
        ),
        self::BOOK_FAIL => array(
            'des' => array('user' => '出票失败，待退款'),
            'str' => 'BookFail',
            'adminOpStatus' => array(self::BOOK_FAIL_RFDING)
        ),
        self::BOOK_SUCC => array(
            'des' => array('user' => '出票成功'),
            'str' => 'BookSucc',
            'userStatus' => array(self::APPLY_RFD)
        ),
        self::APPLY_RFD => array(
            'des' => array('user' => '已申请退票'),
            'str' => 'ApplyRfd',
            'adminHdStatus' => array(self::APPLY_RFD_FAIL)
        ),
    );
    
    public static function getUserDes($status) {
        return self::$flightStatus[$status]['des']['user'];
    }
    
    public static function getAdminDes($status) {
        return isset(self::$flightStatus[$status]['des']['admin']) ? self::$flightStatus[$status]['des']['admin'] : self::getUserDes($status);
    }
    
    public static function getUserStatus($status) {
        return isset(self::$flightStatus[$status]['userStatus']) ? self::$flightStatus[$status]['userStatus'] : array();
    }
    
    public static function getAdminHdStatus($status) {
        return isset(self::$flightStatus[$status]['adminHdStatus']) ? self::$flightStatus[$status]['adminHdStatus'] : array();
    }
    
    public static function getAdminOpStatus($status) {
        return isset(self::$flightStatus[$status]['adminOpStatus']) ? self::$flightStatus[$status]['adminOpStatus'] : array();
    }
}