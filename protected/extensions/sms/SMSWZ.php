<?php
class SMSWZ implements SMSInterface {
    const URL = 'http://223.6.255.39:7895/SendMT/SendMessage';
    const USER = 'qumaiya';
    const PASSWD = 'qumaiya@151215#';
    const TIMEOUT = 8;
    
    public static $_curl = Null;
    public function __construct() {
        if (!self::$_curl) {
            self::$_curl = Curl::getInstance(__CLASS__);
        }
    }
    
    public function getNum($content) {
        $len = mb_strlen($content, 'UTF-8');
        return $len > 70 ? ceil(($len - 70) / 67) + 1 : 1;
    }
    
    private function _send($content, $mobile) {
        $postParams = array(
            'clientID' => self::USER,
            'share_secret' => self::PASSWD,
            'messageList' => array(
                array(
                    'content' => urlencode($content),
                    'mobiles' => $mobile
                )
            )
        );

        if (QEnv::IS_SEND_SMS) {
            $res = self::$_curl->postJ(self::URL, array('param' => json_encode($postParams)));
            if (!Curl::isCorrect($res)) {
                return F::errReturn(F::getCurlError($res));
            }
        }
        
        return F::corReturn();
    }
    
    public function send($params, $type) {
        if (!F::isCorrect($res = SMSTemplate::t($type, $params))) {
            return $res;
        }
        
        $data = $res['data'];
        
        //只要进入到发送就返回corReturn 通过secceed参数通知成功与失败
        $res = $this->_send(SMS::$signs[$data['params']['sign']] . $data['content'], $data['params']['mobile']);
        $data['params']['succeed'] = F::isCorrect($res) ? Dict::STATUS_TRUE : Dict::STATUS_FALSE;
        $data['params']['content'] = $data['content'];

        return F::corReturn($data['params']);
    }
}