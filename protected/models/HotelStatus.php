<?php
class HotelStatus {
    const WAIT_CHECK = 1;
    const CHECK_FAIL = 2;
    const CHECK_SUCC = 3;
    const WAIT_PAY = 4; //个人票使用
    const PAYED = 5; //个人票使用
    const CANCELED = 6;
    const BOOK_PUSHED = 7; //需要向API系统推送
    const BOOK_FAIL = 8; //如果为个人票，则直接跳为BOOK_FAIL_WAIT_RFD
    const BOOK_SUCC = 9;
    const BOOK_FAIL_WAIT_RFD = 10; //个人票使用
    const BOOK_FAIL_RFDING = 11; //个人票使用
    const BOOK_FAIL_RFDED = 12; //个人票使用
    const APPLY_RFD = 23;
    const RFD_PUSHED = 24;
    const RFD_REFUSE = 25;
    const RFD_AGREE = 26;//退票操作需要接单、退款操作无需接单
    const RFDED = 27;
    
    public static $hotelStatus = array(
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
                    'sysHdStatus' => array(self::BOOK_PUSHED),
            ),
            self::WAIT_PAY => array(
                    'des' => array('user' => '待支付'),
                    'str' => 'WaitPay',
                    'userStatus' => array(self::CANCELED, self::PAYED)
            ),
            self::PAYED => array(
                    'des' => array('user' => '已支付'),
                    'str' => 'Payed',
                    'sysHdStatus' => array(self::BOOK_PUSHED)
            ),
            self::CANCELED => array(
                    'des' => array('user' => '已取消'),
                    'str' => 'Canceled'
            ),
            self::BOOK_PUSHED => array(
                    'des' => array('user' => '正在出票'),
                    'str' => 'BookPushed',
                    'sysOpStatus' => array(self::BOOK_SUCC, self::BOOK_FAIL)
            ),
            self::BOOK_FAIL => array(
                    'des' => array('user' => '出票失败'),
                    'str' => 'BookFail',
                    'btn' => '出票失败',
                    'btnColor' => 'danger'
            ),
            self::BOOK_SUCC => array(
                    'des' => array('user' => '出票成功'),
                    'str' => 'BookSucc',
                    'userStatus' => array(self::APPLY_RFD),
                    'btn' => '出票成功',
                    'btnColor' => 'success'
            ),
            self::BOOK_FAIL_WAIT_RFD => array(
                    'des' => array('user' => '预定失败，等待退款'),
                    'str' => 'BookFailWaitRfd',
                    'sysHdStatus' => array(self::BOOK_FAIL_RFDING)
            ),
            self::BOOK_FAIL_RFDING => array(
                    'des' => array('user' => '预定失败，正在退款'),
                    'str' => 'BookFailRfding',
                    'sysOpStatus' => array(self::BOOK_FAIL_RFDED),
            ),
            self::BOOK_FAIL_RFDED => array(
                    'des' => array('user' => '预定失败，已退款'),
                    'str' => 'BookFailRfded',
                    'btn' => '退款成功'
            ),
            self::APPLY_RFD => array(
                    'des' => array('user' => '申请退房'),
                    'str' => 'ApplyRfd',
                    'sysHdStatus' => array(self::RFD_PUSHED)
            ),
            self::RFD_PUSHED => array(
                    'des' => array('user' => '正在退款'),
                    'str' => 'RfdPushed',
                    'sysOpStatus' => array(self::RFD_AGREE, self::RFD_REFUSE)
            ),
            self::RFD_REFUSE => array(
                    'des' => array('user' => '退房失败'),
                    'str' => 'RfdRefuse',
                    'btn' => '拒绝退票',
                    'btnColor' => 'danger'
            ),
            self::RFD_AGREE => array(
                    'des' => array('user' => '正在退款'),
                    'str' => 'RfdAgree',
                    'btn' => '同意退票',
                    'btnColor' => 'success'
            ),
            self::RFDED => array(
                    'des' => array('user' => '已退款'),
                    'str' => 'Rfded',
                    'isJumpCheck' => True
            ),
    );
    
    public static function getUserDes($status) {
        return self::$hotelStatus[$status]['des']['user'];
    }
    
    public static function getUserCando($orderStatus, $doStatus){
        return isset(HotelStatus::$hotelStatus[$orderStatus]['userStatus']) && in_array($doStatus,HotelStatus::$hotelStatus[$orderStatus]['userStatus']);
    }
    
    public static function isJumpCheck($status) {
        return isset(self::$hotelStatus[$status]['isJumpCheck']) ? self::$hotelStatus[$status]['isJumpCheck'] : False;
    }
    
    public static function isUserOp($fromStatus, $toStatus) {
        return isset(self::$hotelStatus[$fromStatus]['userStatus']) && in_array($toStatus, self::$hotelStatus[$fromStatus]['userStatus']);
    }
    
    public static function isSysHd($fromStatus, $toStatus) {
        return isset(self::$hotelStatus[$fromStatus]['sysHdStatus']) && in_array($toStatus, self::$hotelStatus[$fromStatus]['sysHdStatus']);
    }
    
    public static function isSysOp($fromStatus, $toStatus) {
        return isset(self::$hotelStatus[$fromStatus]['sysOpStatus']) && in_array($toStatus, self::$hotelStatus[$fromStatus]['sysOpStatus']);
    }
    
    public static function getCheckFunc($status) {
        return isset(self::$hotelStatus[$status]['check']) ? self::$hotelStatus[$status]['check'] : False;
    }
}