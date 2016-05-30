<?php
class RC {
    const RC_SUCCESS = 0;
    
    const RC_ERROR = 100001;
    const RC_FORBIDDEN = 100002;
    const RC_NO_BUSINESS = 100003;
    const RC_DB_ERROR = 100004;
    const RC_VAR_ERROR = 100005;
    const RC_FILE_PRIVILETE = 100006;
    const RC_DB_TRANSACTION_ERROR = 100007;
    const RC_AUTH_ERROR = 100008;
    const RC_NO_CHANGE = 100009;
    const RC_LOGIN_FAILED = 100010;
    const RC_MODEL_CREATE_ERROR = 100011;
    const RC_MODEL_UPDATE_ERROR = 100012;
    const RC_MODEL_DELETE_ERROR = 100013;
    
    const RC_EXT_CURL_REQUEST_ERROR = 100101;
    const RC_EXT_CURL_JSON_ERROR = 100102;
    const RC_EXT_CURL_SERVER_ERROR = 100103;
    
    const RC_COM_CREATE_ERROR = 100201;
    const RC_COM_HAD_EXISTS = 100202;
    const RC_COM_NOT_EXISTS = 100203;
    const RC_DEP_CREATE_ERROR = 100204;
    const RC_DEP_HAD_EXISTS = 100205;
    const RC_DEP_NOT_EXISTS = 100206;
    const RC_USER_CREATE_ERROR = 100207;
    const RC_USER_HAD_EXISTS = 100208;
    const RC_USER_NOT_EXISTS = 100209;
    const RC_USER_CHANGE_PASSWD_ERROR = 100210;
    const RC_CONTACTER_HAD_EXISTS = 100211;
    const RC_CONTACTER_NOT_EXISTS = 100212;
    const RC_CONTACTER_CREATE_ERROR = 100213;
    const RC_CONTACTER_MODIFY_ERROR = 100214;
    const RC_CONTACTER_DELETE_ERROR = 100215;
    const RC_ADDRESS_HAD_EXISTS = 100216;
    const RC_ADDRESS_NOT_EXISTS = 100217;
    const RC_ADDRESS_CREATE_ERROR = 100218;
    const RC_ADDRESS_MODIFY_ERROR = 100219;
    const RC_ADDRESS_DELETE_ERROR = 100220;
    const RC_ADDRESS_PCC_NOT_EXISTS = 100221;
    const RC_PASSENGER_HAD_EXISTS = 100222;
    const RC_PASSENGER_NOT_EXISTS = 100223;
    const RC_PASSENGER_CREATE_ERROR = 100224;
    const RC_PASSENGER_MODIFY_ERROR = 100225;
    const RC_PASSENGER_DELETE_ERROR = 100226;
    const RC_ORDER_NOT_EXISTS = 100227;
    const RC_USER_UPLOAD_ERROR = 100228;
    const RC_USER_UPLOAD_AVATER_ERROR = 100229;
    const RC_USER_SET_DEVICETOKEN_ERROR = 100230;
    const RC_USER_NOT_REVIEWER = 100231;
    const RC_STATUS_NOT_EXISTS = 100232;
    const RC_STATUS_NOT_OP = 100233;
    const RC_STATUS_NO_OPERATER = 100234;
    const RC_STATUS_CHANGE_ERROR = 100235;
    const RC_REASON_ERROR = 100236;

    const RC_P_ERROR = 100301;
    
    const RC_F_ROUTE_NOT_EXISTS = 100401;
    const RC_F_SEGMENT_ERROR = 100402;
    const RC_F_INFO_CHANGED = 100403;
    const RC_F_NO_SUCH_CABIN = 100404;
    const RC_F_PRICE_ERROR = 100405;
    const RC_F_PASSENGER_NUM_ERROR = 100406;
    const RC_F_CABIN_NUM_ERROR = 100407;
    
    const RC_SMS_LOG_NOT_EXISTS = 100501;
    const RC_SMS_HAD_SENDED = 100501;
    const RC_SMS_SEND_ERROR = 100502;
    const RC_SMS_LIMIT_ERROR = 100503;
    const RC_SMS_INTERVAL_ERROR = 100504;
    const RC_SMS_CODE_HAD_SENDED = 100505;
    const RC_SMS_CODE_NOT_EXISTS = 100506;
    const RC_SMS_CODE_NOT_CORRECT = 100507;
    
    public static function getMsg($status) {
        $config = array(
            self::RC_ERROR => '未知错误',
            self::RC_FORBIDDEN => '没有权限',
            self::RC_AUTH_ERROR => '验证失败',
            self::RC_NO_CHANGE => '数据没有变化',
            self::RC_DB_ERROR => '保存数据失败',
            self::RC_DB_TRANSACTION_ERROR => '保存数据事务失败',
            self::RC_VAR_ERROR => '参数错误',
            self::RC_LOGIN_FAILED => '用户名或密码错误',
            
            self::RC_COM_HAD_EXISTS => '企业已存在',
            self::RC_COM_NOT_EXISTS => '企业不存在',
            self::RC_DEP_HAD_EXISTS => '部门已存在',
            self::RC_DEP_NOT_EXISTS => '部门不存在',
            self::RC_USER_HAD_EXISTS => '员工已存在',
            self::RC_USER_NOT_EXISTS => '员工不存在',
        );
    
        return isset($config[$status]) ? $config[$status] : '服务器异常，请稍后再试';
    }
}