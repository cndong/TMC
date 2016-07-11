<?php
class TrainStatus {
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
    const APPLY_RSN = 13;
    const RSN_PUSHED = 14;
    const RSN_REFUSE = 15;
    const RSN_RSNEDING = 16; //原票改为此状态 *票*
    const RSN_AGREE = 17; //需要填写要改签的航班信息和要改签的乘客 *订单+票*
    const RSN_NED_PAY = 18; //个人票使用
    const RSN_NED_PAY_TIMEOUT = 19; //个人票使用
    const RSN_PAYED = 20; //个人票使用
    const RSN_SUCC = 21; //因公票此步计算差额 新票为此状态
    const RSNED = 22; //原票改为已改签状态 *订单+票*
    const APPLY_RFD = 23;
    const RFD_PUSHED = 24;
    const RFD_REFUSE = 25;
    const RFD_AGREE = 26;//退票操作需要接单、退款操作无需接单
    const RFDED = 27;
    
    public static $trainStatus = array(
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
            'sysOpStatus' => array(self::BOOK_SUCC, self::BOOK_FAIL),
            'btn' => '重新推送'
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
            'userStatus' => array(self::APPLY_RSN, self::APPLY_RFD),
            'btn' => '出票成功',
            'btnColor' => 'success'
        ),
        self::BOOK_FAIL_WAIT_RFD => array(
            'des' => array('user' => '出票失败，等待退款'),
            'str' => 'BookFailWaitRfd',
            'sysHdStatus' => array(self::BOOK_FAIL_RFDING)
        ),
        self::BOOK_FAIL_RFDING => array(
            'des' => array('user' => '订票失败，正在退款'),
            'str' => 'BookFailRfding',
            'sysOpStatus' => array(self::BOOK_FAIL_RFDED),
        ),
        self::BOOK_FAIL_RFDED => array(
            'des' => array('user' => '订票失败，已退款'),
            'str' => 'BookFailRfded',
            'btn' => '退款成功'
        ),
        self::APPLY_RSN => array(
            'des' => array('user' => '已申请改签'),
            'str' => 'ApplyRsn',
            //'check' => 'isCanApplyResign',
            'sysHdStatus' => array(self::RSN_PUSHED)
        ),
        self::RSN_PUSHED => array(
            'des' => array('user' => '正在改签'),
            'str' => 'RsnPushed',
            'sysOpStatus' => array(self::RSN_AGREE, self::RSN_REFUSE),
            'btn' => '重新推送'
        ),
        self::RSN_AGREE => array(
            'des' => array('user' => '正在改签', 'admin' => '已同意改签'),
            'str' => 'RsnAgree',
            'sysOpStatus' => array(self::RSN_SUCC),
            'btn' => '同意改签',
            'btnColor' => 'success'
        ),
        self::RSN_REFUSE => array(
            'des' => array('user' => '改签失败'),
            'str' => 'RsnRefuse',
            'btn' => '拒绝改签',
            'btnColor' => 'danger'
        ),
        self::RSN_RSNEDING => array(
            'des' => array('user' => '正在改签', 'admin' => '正在改签(票)')
        ),
        self::RSN_NED_PAY => array(
            'des' => array('user' => '改签中，需支付差额'),
            'str' => 'RsnNedPay',
            'userStatus' => array(self::RSN_PAYED),
            'sysOpStatus' => array(self::RSN_NED_PAY_TIMEOUT)
        ),
        self::RSN_PAYED => array(
            'des' => array('user' => '改签中，已支付差额'),
            'str' => 'RsnPayed',
            'sysOpStatus' => array(self::RSN_SUCC),
        ),
        self::RSN_NED_PAY_TIMEOUT => array(
            'des' => array('user' => '改签已超时'),
            'str' => 'RsnNedPayTimeout',
            'btn' => '支付超时'
        ),
        self::RSN_PAYED => array(
            'des' => array('user' => '已支付改签差额'),
            'str' => 'RsnPayed',
            'sysOpStatus' => array(self::RSN_SUCC)
        ),
        self::RSN_SUCC => array(
            'des' => array('user' => '改签票'),
            'str' => 'RsnSucc',
            'btn' => '改签成功',
            'btnColor' => 'success'
        ),
        self::RSNED => array(
            'des' => array('user' => '已改签'),
            'str' => 'Resed',
            'userStatus' => array(self::APPLY_RFD)
        ),
        self::APPLY_RFD => array(
            'des' => array('user' => '申请退票'),
            'str' => 'ApplyRfd',
            'isJumpCheck' => True,
            'isOrder' => False,
            'isTicket' => True,
            //'check' => 'isCanApplyRefund',
            'sysHdStatus' => array(self::RFD_PUSHED)
        ),
        self::RFD_PUSHED => array(
            'des' => array('user' => '正在退款'),
            'str' => 'RfdPushed',
            'isJumpCheck' => True,
            'isOrder' => False,
            'isTicket' => True,
            'sysOpStatus' => array(self::RFD_AGREE, self::RFD_REFUSE),
            'btn' => '重新推送'
        ),
        self::RFD_REFUSE => array(
            'des' => array('user' => '退票失败'),
            'str' => 'RfdRefuse',
            'isJumpCheck' => True,
            'isOrder' => False,
            'isTicket' => True,
            'btn' => '拒绝退票',
            'btnColor' => 'danger'
        ),
        self::RFD_AGREE => array(
            'des' => array('user' => '正在退款'),
            'str' => 'RfdAgree',
            'isJumpCheck' => True,
            'isOrder' => False,
            'isTicket' => True,
            'btn' => '同意退票',
            'btnColor' => 'success'
        ),
        self::RFDED => array(
            'des' => array('user' => '已退款'),
            'str' => 'Rfded',
            'isJumpCheck' => True,
            'isOrder' => False,
            'isTicket' => True
        ),
    );

    public static $trainStatusGroup = array(
        'waitCheck' => array(self::WAIT_CHECK)
    );
    
    public static function isTrainStatus($status) {
        return isset(self::$trainStatus[$status]);
    }
    
    public static function isTrainStatusArray($statusArray) {
        foreach ($statusArray as $status) {
            if (!self::isTrainStatus($status)) {
                return False;
            }
        }
        
        return True;
    }
    
    public static function isUserOp($fromStatus, $toStatus) {
        return isset(self::$trainStatus[$fromStatus]['userStatus']) && in_array($toStatus, self::$trainStatus[$fromStatus]['userStatus']);
    }
    
    public static function isSysHd($fromStatus, $toStatus) {
        return isset(self::$trainStatus[$fromStatus]['sysHdStatus']) && in_array($toStatus, self::$trainStatus[$fromStatus]['sysHdStatus']);
    }
    
    public static function isSysOp($fromStatus, $toStatus) {
        return isset(self::$trainStatus[$fromStatus]['sysOpStatus']) && in_array($toStatus, self::$trainStatus[$fromStatus]['sysOpStatus']);
    }
    
    public static function isOrderStatus($status) {
        return isset(self::$trainStatus[$status]['isOrder']) ? self::$trainStatus[$status]['isOrder'] : True;
    }
    
    public static function isTicketStatus($status) {
        return isset(self::$trainStatus[$status]['isTicket']) ? self::$trainStatus[$status]['isTicket'] : False;
    }
    
    public static function isJumpCheck($status) {
        return isset(self::$trainStatus[$status]['isJumpCheck']) ? self::$trainStatus[$status]['isJumpCheck'] : False;
    }
    
    public static function getUserDes($status) {
        return self::$trainStatus[$status]['des']['user'];
    }
    
    public static function getAdminDes($status) {
        return isset(self::$trainStatus[$status]['des']['admin']) ? self::$trainStatus[$status]['des']['admin'] : self::getUserDes($status);
    }
    
    public static function getUserStatus($status) {
        return isset(self::$trainStatus[$status]['userStatus']) ? self::$trainStatus[$status]['userStatus'] : array();
    }
    
    public static function getSysHdStatus($status) {
        return isset(self::$trainStatus[$status]['sysHdStatus']) ? self::$trainStatus[$status]['sysHdStatus'] : array();
    }
    
    public static function getSysOpStatus($status) {
        return isset(self::$trainStatus[$status]['sysOpStatus']) ? self::$trainStatus[$status]['sysOpStatus'] : array();
    }
    
    public static function getCheckFunc($status) {
        return isset(self::$trainStatus[$status]['check']) ? self::$trainStatus[$status]['check'] : False;
    }
    
    public static function getCanResignOrderStatus() {
        return array(self::BOOK_SUCC);
    }
    
    public static function getCanResignTicketStatus() {
        return array(self::BOOK_SUCC);
    }
    
    public static function getCanRefundOrderStatus() {
        return array(self::BOOK_SUCC, self::RSNED);
    }
    
    public static function getCanRefundTicketStatus() {
        return array(self::BOOK_SUCC, self::RSN_SUCC);
    }
    
    public static function getCanRefundPushTicketStatus() {
        return array(self::APPLY_RFD);
    }
    
    public static function getCanRefundResTicketStatus() {
        return array(self::RFD_PUSHED);
    }
    
    public static function getCanRefundedTicketStatus() {
        return array(self::RFD_AGREE);
    }
    
    public static function getRefundingTicketStatus() {
        return array(self::RFD_AGREE, self::RFDED);
    }
}