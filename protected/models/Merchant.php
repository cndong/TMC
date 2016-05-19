<?php
class Merchant {
    const MERCHANT_WEB = 1;
    const MERCHANT_MOBILE = 2;
    const MERCHANT_IOS = 3;
    const MERCHANT_ANDROID = 4;

    public static $merchants = array(
        self::MERCHANT_WEB => array(
            'name' => '网页端'
        ),
        self::MERCHANT_MOBILE => array(
            'name' => '手机端'
        ),
        self::MERCHANT_IOS => array(
            'name' => 'IOS'
        ),
        self::MERCHANT_ANDROID => array(
            'name' => 'Android'
        )
    );

    public static function getMerchants($merchantID = Null) {
        $merchants = F::mergeArrayInt(self::$merchants, QEnv::$sources);
        if ($merchantID) {
            return $merchants[$merchantID];
        }
        
        return $merchants;
    }

    public static function isMerchantID($merchantID) {
        return isset(self::$merchants[$merchantID]);
    }
}