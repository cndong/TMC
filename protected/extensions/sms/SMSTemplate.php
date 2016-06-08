<?php
class SMSTemplate {
    const COMMON = 1;
    const FORGET_PASSWD = 2;
    const NEW_USRE = 3;
    
    public static $templates = array(
        self::COMMON => array(
            'name' => '普通信息',
            'limit' => 10,
            'limitUnit' => '1d',
            'interval' => 60,
            'template' => '<{content}>',
            'formats' => array(
                'content' => ParamsFormat::TEXTNZ
            ),
        ),
        self::FORGET_PASSWD => array(
            'name' => '忘记密码',
            'limit' => 10,
            'limitUnit' => '1d',
            'interval' => 60,
            'template' => '尊敬的用户您好！您重置密码的验证码为：<{code}>',
            'formats' => array(
                'code' => ParamsFormat::SMS_CODE
            )
        ),
        self::NEW_USRE => array(
            'name' => '新建用户账号',
            'limit' => 10,
            'limitUnit' => '1d',
            'interval' => 60,
            'template' => '尊敬的用户[<{name}>]您好, 您的登录账号: <{mobile}>, 初始密码: <{password}>。登录后请修改初始密码，APP下载链接: http://dwz.cn/sfbm_tmc',
            'formats' => array(
                'name' => ParamsFormat::TEXTNZ,
                'mobile' => ParamsFormat::MOBILE,
                'password' => ParamsFormat::TEXTNZ,
            )
        ),
    );
    
    public static function getLimitUnitTime($type) {
        $limitUnit = self::$templates[$type]['limitUnit'];
        $num = substr($limitUnit, 0, -1);
        $unit = substr($limitUnit, -1);
        
        switch ($unit) {
            case 'Y':
                $num *= 365;
            case 'm':
                $num *= 30;
            case 'd':
                $num *= 24;
            case 'H':
                $num *= 60;
            case 'M':
                $num *= 60;
            case 'S':
                $num *= 60;
        }
        
        return $num;
    }
    
    public static function t($type, $params) {
        $formats = self::$templates[$type]['formats'];
        $formats['mobile'] = ParamsFormat::MOBILE;
        $formats['sign'] = '!' . ParamsFormat::SMS_SIGN . '--' . SMS::SIGN_QMY;
        if (!($params = F::checkParams($params, $formats))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $tr = array();
        foreach ($params as $field => $value) {
            $tr['<{' . $field . '}>'] = $value;
        }
        
        return F::corReturn(array('params' => $params, 'content' => strtr(self::$templates[$type]['template'], $tr)));
    }
}