<?php
class Provider {
    const PROVIDER_QMY = 1;
    
    public static $providersConfigDefault = array(
        self::PROVIDER_QMY => array('name' => '去买呀', 'str' => 'QMY')
    );
    public static $providersConfig = array(); //子类只需重定义此属性即可
    
    private static $_providerObjects = array();
    
    public static final function getProviderConfig($providerID = Null, $key = Null) {
        $vars = get_class_vars(get_called_class());
        $providers = F::mergeArrayInt(self::$providersConfigDefault, $vars['providersConfig']);
        
        if ($providerID && $key) {
            return $providers[$providerID][$key];
        } elseif ($providerID) {
            return $providers[$providerID];
        }
        
        return $providers;
    }
    
    protected static final function getProvider($providerID) {
        $className = get_called_class();
        
        $providerStr = self::getProviderConfig($providerID, 'str');
        
        if (!isset(self::$_providerObjects[$className][$providerStr])) {
            $providerClassName = $className . $providerStr;
            self::$_providerObjects[$className][$providerID] = new $providerClassName();
        }
        
        return self::$_providerObjects[$className][$providerID];
    }
}