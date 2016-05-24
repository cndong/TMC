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
        return QEnv::ENV == self::ENV_LOCAL;
    }
    
    public static function isTestEnv() {
        return QEnv::ENV == self::ENV_TEST;
    }
    
    public static function isProductEnv() {
        return QEnv::ENV == self::ENV_PRODUCT;
    }
    
    public static function isMobileHost() {
        $tmp = str_replace('.', '\.', self::HOST_PREFIX_M);
        return preg_match("/(^|\.){$tmp}/", Q_HOST);
    }
    
    public static function isMobilePlatform() {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) 
            return False;
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
    
    public static function getPlatform() {
        return Q::isMobilePlatform() ? Q::PLATFORM_MOBILE : Q::PLATFORM_PC;
    }
    
    public static function getConfig() {
        if (!class_exists('QEnv', False)) {
            require self::getEnvFile();
        }
        $configPath = Q_ROOT_PATH . '/protected/config/';
        
        $hostLower = strtolower(Q_HOST) . '.php';
        if (file_exists($configPath . $hostLower)) {
            return $configPath . $hostLower;
        }
        
        $config = require($configPath . 'base.php');
        foreach (array(QEnv::ENV, Q::getPlatform()) as $file) {
            $file = $configPath . $file . '.php';
            if (file_exists($file)) {
                $config = CMap::mergeArray($config, require($file));
            }
        }
        
        return $config;
    }
    
    public static function getDataDocFile($fileName) {
        return implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'datas', 'doc', $fileName));
    }
    
    public static function getUniqueID($time = Q_TIME, $providerID = '0', $maxLength = 3) {
        $key = KeyManager::getUniqueIDKey($time, $providerID);
        $mem = Yii::app()->cache->getMemCache();
        if (Yii::app()->cache->useMemcached) {
            $mem->add($key, 0, 5);
        } else {
            $mem->add($key, 0, False, 5);
        }
        
        $num = $mem->increment($key);
        if (!$num || strlen($num) > $maxLength) {
            return False;
        }
        
        $tmp = getdate($time);
        $time = str_pad(strval($time % 3600), 4, '0', STR_PAD_LEFT);
        
        $rtn = $tmp['year'] - 2015;
        $rtn .= str_pad($tmp['mon'], 2, '0', STR_PAD_LEFT);
        $rtn .= str_pad($tmp['mday'], 2, '0', STR_PAD_LEFT);
        $rtn .= str_pad($tmp['hours'], 2, '0', STR_PAD_LEFT);
        $rtn .= $providerID;
        $rtn .= $time{2};
        $rtn .= $time{1};
        $rtn .= $time{0};
        $rtn .= str_pad($num, $maxLength, '0', STR_PAD_LEFT);
        $rtn .= $time{3};
        
        return strtoupper($rtn);
    }
    
    public static function log($message = '', $cat = 'application', $request = False,  $level = CLogger::LEVEL_ERROR){
        if(!is_string($message)) $message = var_export($message, True);
        $backtrace = debug_backtrace();
        $request = $request ? ' Request: ' . $_SERVER['REQUEST_URI'] . '|' . json_encode($_POST) . '|' . json_encode($_FILES) : '';
        if (isset($backtrace[1]['file'])) {
            $request .= "[{$backtrace[1]['file']}]-[{$backtrace[1]['line']}]";
        }
        Yii::log($message . ' @' . F::getClientIP() . $request, CLogger::LEVEL_ERROR, $cat, 0);
    }
    
    public static function logModel($model) {
        $category = 'dberror.' . get_class($model);
        Q::log('---------数据库操作失败开始---------', $category);
        Q::log($model->attributes, $category);
        Q::log($model->getErrors(), $category);
        Q::log('---------数据库操作失败结束---------', $category);
    }
}