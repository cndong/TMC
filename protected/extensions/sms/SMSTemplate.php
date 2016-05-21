<?php
class SMSTemplate {
    const COMMON = 1;
    const FORGET_PASSWD = 2;
    
    public static $templates = array(
        self::COMMON => array(
            'name' => '普通信息',
            'limit' => 10,
            'interval' => 60,
            'template' => '<{content}>',
            'formats' => array(
                'content' => ParamsFormat::TEXTNZ
            ),
        ),
        self::FORGET_PASSWD => array(
            'name' => '忘记密码',
            'limit' => 10,
            'interval' => 60,
            'template' => '尊敬的用户您好！您重置密码的验证码为：<{code}>',
            'formats' => array(
                'code' => ParamsFormat::SMS_CODE
            )
        ),
    );
    
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