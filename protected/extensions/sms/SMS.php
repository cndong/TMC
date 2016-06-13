<?php
/*
 * 随永杰 2016-06-05
 */
class SMS {
    const PROVIDER_WZ = 1;
    public static $providers = array(
        self::PROVIDER_WZ => array('name' => '未知', 'str' => 'WZ')
    );
    
    const SIGN_QMY = 1;
    const SIGN_BUS = 2;
    public static $signs = array(
        self::SIGN_QMY => '【十分便民】',
        self::SIGN_BUS => '【松鼠巴士】'
    );
    
    private static $_instances = array();
    protected static final function getProvider($providerID) {
        $className = get_called_class();
        
        $providerStr = self::$providers[$providerID]['str'];
        if (!isset(self::$_instances[$className][$providerStr])) {
            $providerClassName = $className . $providerStr;
            self::$_instances[$className][$providerID] = new $providerClassName();
        }
        
        return self::$_instances[$className][$providerID];
    }
    
    public static function isSMSSign($sign) {
        return isset(self::$signs[$sign]);
    }
    
    public static function send($params, $type = '') {
        if ($tmp = F::checkParams($params, array('smsID' => ParamsFormat::INTNZ))) {
            if (!$smsLog = SMSLog::model()->findByPk($tmp['smsID'])) {
                return F::errReturn(RC::RC_SMS_LOG_NOT_EXISTS);
            }
            
            if ($smsLog->succeed) {
                return F::errReturn(RC::RC_SMS_HAD_SENDED);
            }
            
            $params = F::arrayGetByKeys($smsLog, array('content', 'sign'));
            $type = SMSTemplate::COMMON;
        } else {
            if (!F::checkParams($params, array('mobile' => ParamsFormat::MOBILE))) {
                return F::errReturn(RC::RC_VAR_ERROR);
            }
            $tmp = SMSLog::getByLimitUnit($params['mobile'], $type, Dict::STATUS_TRUE);
            if (count($tmp) >= SMSTemplate::$templates[$type]['limit']) {
                return F::errReturn(RC::RC_SMS_LIMIT_ERROR);
            } else if (!empty($tmp)) {
                $tmp = current($tmp);
                if (Q_TIME - $tmp->ctime < SMSTemplate::$templates[$type]['interval']) {
                    return F::errReturn(RC::RC_SMS_INTERVAL_ERROR);
                }
            }
        }
        
        $smsLog = !empty($smsLog) ? $smsLog : new SMSLog();
        if (!F::isCorrect($res = self::getProvider(self::PROVIDER_WZ)->send($params, $type))) {
            return $res;
        }
        
        if (!$smsLog->isNewRecord) {
            $smsLog->succeed = $res['data']['succeed'];
        } else {
            $smsLog->type = $type;
            $smsLog->attributes = $res['data'];
        }
        
        if (!$smsLog->save()) {
            Q::logModel($smsLog);
            
            return F::errReturn(RC::RC_MODEL_UPDATE_ERROR);
        }
        
        return F::corReturn();
    }
}