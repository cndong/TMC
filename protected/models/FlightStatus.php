<?php
class FlightStatus {
    const WAIT_CHECK = 1;
    const CHECK_FAIL = 2;
    const CHECK_SUCC = 3;
    const WAIT_PAY = 4; //个人票使用
    const PAYED = 5; //个人票使用
    const CANCELED = 6;
    const BOOKING = 7;
    const BOOK_FAIL = 8; //如果为个人票，则直接跳为BOOK_FAIL_WAIT_RFD
    const BOOK_SUCC = 9;
    const BOOK_FAIL_WAIT_RFD = 10; //个人票使用
    const BOOK_FAIL_RFDING = 11; //个人票使用
    const BOOK_FAIL_RFDED = 12; //个人票使用
    const APPLY_RSN = 13;
    const RSNING = 14;
    const RSN_FAIL = 15;
    const RSN_NED_PAY = 16; //个人票使用
    const RSN_PAYED = 17; //个人票使用
    const RSN_SUCC = 18; //因公票此步计算差额 新票为此状态
    const RESED = 19; //原票改为已改签状态
    const APPLY_RFD = 20;
    const RFDING = 21;
    const RFD_FAIL = 22;
    const RFD_SUCC = 22;
    const RFD_ADM_RFDING = 24;
    const RFD_ADM_RFDED = 25;
    
    public static $flightStatus = array(
        self::WAIT_CHECK => array(
            'des' => array('user' => '待审批'),
            'str' => 'WaitCheck',
            'userStatus' => array(self::CHECK_FAIL, self::CHECK_SUCC, self::CANCELED)
        ),
        self::CHECK_FAIL => array(
            'des' => array('user' => '未通过'),
            'str' => 'CheckFail'
        ),
        self::CHECK_SUCC => array(
            'des' => array('user' => '已通过'),
            'str' => 'CheckSucc',
            'userStatus' => array(self::CANCELED),
            'adminHdStatus' => array(self::BOOKING),
        ),
        self::WAIT_PAY => array(
            'des' => array('user' => '待支付'),
            'str' => 'WaitPay',
            'userStatus' => array(self::CANCELED, self::PAYED)
        ),
        self::PAYED => array(
            'des' => array('user' => '已支付'),
            'str' => 'Payed',
            'adminHdStatus' => array(self::BOOKING)
        ),
        self::CANCELED => array(
            'des' => array('user' => '已取消'),
            'str' => 'Canceled'
        ),
        self::BOOKING => array(
            'des' => array('user' => '正在出票'),
            'str' => 'Booking',
            'adminOpStatus' => array(self::BOOK_FAIL, self::BOOK_SUCC)
        ),
        self::BOOK_FAIL => array(
            'des' => array('user' => '出票失败'),
            'str' => 'BookFail',
            'btn' => '出票失败'
        ),
        self::BOOK_SUCC => array(
            'des' => array('user' => '出票成功'),
            'str' => 'BookSucc',
            'userStatus' => array(self::APPLY_RSN, self::APPLY_RFD),
            'btn' => '出票成功'
        ),
        self::BOOK_FAIL_WAIT_RFD => array(
            'des' => array('user' => '出票失败，等待退款'),
            'str' => 'BookFailWaitRfd',
            'adminHdStatus' => array(self::BOOK_FAIL_RFDING)
        ),
        self::BOOK_FAIL_RFDING => array(
            'des' => array('user' => '订票失败，正在退款'), //需要加个check判断是否是私人的 然后退款
            'str' => 'BookFailRfding',
            'adminOpStatus' => array(self::BOOK_FAIL_RFDED),
        ),
        self::BOOK_FAIL_RFDED => array(
            'des' => array('user' => '订票失败，已退款'),
            'str' => 'BookFailRfded',
            'btn' => '退款成功'
        ),
        self::APPLY_RSN => array(
            'des' => array('user' => '已申请改签'),
            'str' => 'ApplyRsn',
            'adminHdStatus' => array(self::RSNING)
        ),
        self::RSNING => array(
            'des' => array('user' => '正在改签'),
            'str' => 'Rsning',
            'adminOpStatus' => array(self::RSN_FAIL, self::RSN_NED_PAY, self::RSN_SUCC, self::RESED)
        ),
        self::RSN_FAIL => array(
            'des' => array('user' => '改签失败'),
            'str' => 'RsnFail' //虚状态需要改为订票成功
        ),
        self::RSN_NED_PAY => array( //需要加个check判断是否是私人的才显示
            'des' => array('user' => '改签需支付'),
            'str' => 'RsnNedPay',
            'userStatus' => array(self::RSN_PAYED)
        ),
        self::RSN_PAYED => array(
            'des' => array('user' => '已支付改签差额'),
            'str' => 'RsnPayed',
            'adminOpStatus' => array(self::RSN_SUCC)
        ),
        self::RSN_SUCC => array(
            'des' => array('user' => '改签成功'),
            'str' => 'RsnSucc'
        ),
        self::RESED => array(
            'des' => array('user' => '已改签'),
            'str' => 'Resed',
        ),
        self::APPLY_RFD => array(
            'des' => array('user' => '申请退票'),
            'str' => 'ApplyRfd',
            'adminHdStatus' => array(self::RFDING)
        ),
        self::RFDING => array(
            'des' => array('user' => '正在退票'),
            'str' => 'Rfding',
            'adminOpStatus' => array(self::RFD_FAIL, self::RFD_SUCC)
        ),
        self::RFD_FAIL => array(
            'des' => array('user' => '退票失败'),
            'str' => 'RfdFail'
        ),
        self::RFD_SUCC => array(
            'des' => array('user' => '等待退款'),
            'str' => 'RfdSucc',
            'adminHdStatus' => array(self::RFD_ADM_RFDING)
        ),
        self::RFD_ADM_RFDING => array(
            'des' => array('user' => '正在退款'),
            'str' => 'RfdAdmRfding',
            'adminOpStatus' => array(self::RFD_ADM_RFDED)
        ),
        self::RFD_ADM_RFDED => array(
            'des' => array('user' => '已退款'),
            'str' => 'RfdAdmRfded'
        ),
    );

    public static $flightStatusGroup = array(
        'waitCheck' => array(self::WAIT_CHECK)
    );
    
    public static function isFlightStatus($status) {
        return isset(self::$flightStatus[$status]);
    }
    
    public static function isFlightStatusArray($statusArray) {
        foreach ($statusArray as $status) {
            if (!self::isFlightStatus($status)) {
                return False;
            }
        }
        
        return True;
    }
    
    public static function isUserOp($fromStatus, $toStatus) {
        return isset(self::$flightStatus[$fromStatus]['userStatus']) && in_array($toStatus, self::$flightStatus[$fromStatus]['userStatus']);
    }
    
    public static function isAdminHd($fromStatus, $toStatus) {
        return isset(self::$flightStatus[$fromStatus]['adminHdStatus']) && in_array($toStatus, self::$flightStatus[$fromStatus]['adminHdStatus']);
    }
    
    public static function isAdminOp($fromStatus, $toStatus) {
        return isset(self::$flightStatus[$fromStatus]['adminOpStatus']) && in_array($toStatus, self::$flightStatus[$fromStatus]['adminOpStatus']);
    }
    
    public static function isOrderStatus($status) {
        return isset(self::$flightStatus[$status]['isOrder']) ? self::$flightStatus[$status]['isOrder'] : True;
    }
    
    public static function isTicketStatus($status) {
        return isset(self::$flightStatus[$status]['isTicket']) ? self::$flightStatus[$status]['isTicket'] : False;
    }
    
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
    
    public static function getCheckFunc($status) {
        return isset(self::$flightStatus[$status]['check']) ? self::$flightStatus[$status]['check'] : False;
    }
}