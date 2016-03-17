<?php
class Q {
    public static $_G = array();
    
    const ENV_LOCAL = 'local';
    const ENV_TEST = 'test';
    const ENV_PRODUCT = 'product';
    
    const PLATFORM_MOBILE = 'm';
    const PLATFORM_PC = 'p';
    
    const HOST_PREFIX_M = 'm.';
    
    public static function isLocalEnv() {
        return self::getEnv('env') == self::ENV_LOCAL;
    }
    
    public static function isTestEnv() {
        return self::getEnv('env') == self::ENV_TEST;
    }
    
    public static function isProductEnv() {
        return self::getEnv('env') == self::ENV_PRODUCT;
    }
    
    public static function isMobileHost() {
        $tmp = str_replace('.', '\.', self::HOST_PREFIX_M);
        return preg_match("/(^|\.){$tmp}/", Q_HOST);
    }
    
    public static function isMobilePlatform() {
        if (self::isMobileHost())
            return True;
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
            return True;
        if ((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') !== False))
            return True;
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']))
            return True;
        $mobileAgents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
            'wapr','webc','winw','winw','xda','xda-'
        );
        if (in_array(strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4)), $mobileAgents))
            return True;
        if((isset($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== False) || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== False || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== False)
            return True;
        
        return False;
    }
    
    public static function isPcPlatform() {
        return !self::isMobilePlatform();
    }
    
    public static function getEnvFile() {
        return Q_ROOT_PATH . DIRECTORY_SEPARATOR . 'env.php';
    }
    
    public static function getEnv() {
        static $env = '';
        if (!$env) {
            if (!file_exists($file = self::getEnvFile())) {
                file_put_contents($file, '');
            }
            
            $env = include($file);
        }
        
        return $env;
    }
    
    public static function getPlatform() {
        return Q::isMobilePlatform() ? Q::PLATFORM_MOBILE : Q::PLATFORM_PC;
    }
    
    public static function getConfig() {
        $env = Q::getEnvStr();
        $platform = Q::getPlatformStr();
    
        $configPath = Q_ROOT_PATH . '/protected/config/';
        $configFile = implode('.', array($env, $hostHeader, $platform)) . '.php';
        if (file_exists($configPath . $configFile)) {
            return require($configPath . $configFile);
        }
    
        $config = require($configPath . 'base.php');
        foreach (array($env, $hostHeader, $platform) as $k => $file) {
            if (file_exists($configPath . $file . '.php') || ($k == 1 && file_exists($configPath . Qmy::DEFAULT_HOST_HEADER . '.php') && ($file = Qmy::DEFAULT_HOST_HEADER))) {
                $config = CMap::mergeArray($config, require($configPath . $file . '.php'));
            }
        }
    
        return $config;
    }
    
    public static $return = array(
        'rc' => QR::RC_SUCCESS,
        'msg' => '',
        'data' => array()
    );
    public static function corReturn($data = '') {
        $rtn = self::$return;
        $rtn['data'] = $data;
    
        return $rtn;
    }
    
    public static function errReturn($status, $msg = '') {
        $rtn = self::$return;
        $rtn['rc'] = $status;
        $rtn['msg'] = $msg ? $msg : QR::getMsg($status);
    
        return $rtn;
    }
    
    public static function isCorrect($res) {
        return $res['rc'] == QR::RC_SUCCESS;
    }
    
    public static function getCurlError($res) {
        if (Curl::isRequestError($res)) {
            return QR::RC_EXT_CURL_REQUEST_ERROR;
        }
        if (Curl::isJsonError($res)) {
            return QR::RC_EXT_CURL_JSON_ERROR;
        }
        
        return QR::RC_EXT_CURL_SERVER_ERROR;
    }
}