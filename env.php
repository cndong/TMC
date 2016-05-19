<?php
class QEnv {
    const ENV = Q::ENV_LOCAL;
    
    public static $merchants = array(
        Merchant::MERCHANT_WEB => array(
            'key' => '01e118d5a6c903542bdc873dbe2ed94c',
        ),
        Merchant::MERCHANT_MOBILE => array(
            'key' => '01e118d5a6c903542bdc873dbe2ed94c',
        ),
        Merchant::MERCHANT_IOS => array(
            'key' => '01e118d5a6c903542bdc873dbe2ed94c',
        ),
        Merchant::MERCHANT_ANDROID => array(
            'key' => '01e118d5a6c903542bdc873dbe2ed94c'
        ),
    );
    
    public static $providers = array(
        Dict::BUSINESS_FLIGHT => array(
            ProviderF::PROVIDER_TB => array(
                'appkey' => '23348441',
                'secretKey' => '477b16da1e5ba3d68c75c0fdf46995c2'
            ),
        )
    );
    
    public static $orderParamsKey = array(
        Dict::BUSINESS_FLIGHT => 'ff1f700273159f604c436e37c93c314e',
        Dict::BUSINESS_FLIGHT => 'ff1f700273159f604c436e37c93c314e',
        Dict::BUSINESS_FLIGHT => 'ff1f700273159f604c436e37c93c314e'
    );
}