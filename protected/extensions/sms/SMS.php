<?php
class SMS {
    const PROVIDER_WZ = 1;
    public static $providers = array(
        self::PROVIDER_WZ => array('name' => '未知', 'str' => 'WZ')
    );
    
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
    
    public static function send($params, $type) {
        
    }
}