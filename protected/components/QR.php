<?php
class QR {
    const RC_SUCCESS = 0;
    
    const RC_DB_ERROR = 100001;
    const RC_VAR_ERROR = 100005;
    
    const RC_EXT_CURL_REQUEST_ERROR = 100101;
    const RC_EXT_CURL_JSON_ERROR = 100102;
    const RC_EXT_CURL_SERVER_ERROR = 100103;
    
    public static function getMsg($status) {
        $config = array(
            self::RC_DB_ERROR => '未知错误',
            self::RC_VAR_ERROR => '参数错误'
        );
    
        return isset($config[$status]) ? $config[$status] : '服务器异常，请稍后再试';
    }
}