<?php
class SMS {
    const PROVIDER_WZ = 1;
    public static $providers = array(
        self::PROVIDER_WZ => array('name' => '未知', 'str' => 'WZ')
    );
    
    const SIGN_QMY = 1;
    const SIGN_BUS = 2;
    public static $signs = array(
        self::SIGN_QMY => '【去买呀】',
        self::SIGN_BUS => '【松鼠巴士】'
    );
    
    private static $_instances = array();
    protected static final function getProvider($providerID) {
        $className = get_called_class();
        
        $providerStr = self::$providers[$providerID]['str'];
        if (!isset(self::$_instances[$className][$providerStr])) {
            $providerClassName = $className . $providerStr;
            self::$_providerObjects[$className][$providerID] = new $providerClassName();
        }
        
        return self::$_providerObjects[$className][$providerID];
    }
    
    public static function isSMSSign($sign) {
        return isset(self::$signs[$sign]);
    }
    
    public static function send($params, $type = '') {
        if ($tmp = F::checkParams($params, array('smsID' => ParamsFormat::INTNZ))) {
            if (!$smsLog = SMSLog::model()->findByPk($tmp['smsID'])) {
                return F::errReturn(RC::RC_SMS_LOG_NOT_EXISTS);
            }
            
            if ($sms->succeed) {
                return F::errReturn(RC::RC_SMS_HAD_SENDED);
            }
            
            $params = array('content' => $smsLog->content);
            $type = SMSTemplate::COMMON;
        }
        
        $smsLog = !empty($smsLog) ? $smsLog : new SMSLog();
        
        if (!F::isCorrect($res = self::getProvider(self::PROVIDER_WZ)->send($params, $type))) {
            return $res;
        }
        
        if (!$smsLog->isNewRecord) {
            $smsLog->succeed = $res['data']['succeed'];
        } else {
            $smsLog->attributes = $res['data'];
        }
        
        if (!$smsLog->save()) {
            Q::log('------保存短信发送结果失败开始------');
            Q::log($smsLog->getErrors());
            Q::Log('------保存短信发送结果失败结束------');
            
            return F::errReturn(RC::RC_MODEL_UPDATE_ERROR);
        }
        
        return F::corReturn();
    }
}