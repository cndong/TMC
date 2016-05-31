<?php
const PUSH_ANDROID_KEY = '57495d3ce0f55a85260027dd';
const PUSH_ANDROID_SECRET= 'm8r1vllgdwqzdygqvvllaeas98tjnpfm';
const PUSH_IOS_KEY = '5749300267e58e4b070004b6';
const PUSH_IOS_SECRET = 'nojccfjddrroxedlkhsewpg4cdmlbbzb';

class SystemController extends ApiController {
    public function actionConfig() {
        if (!F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!($user = User::model()->findByPk($_POST['userID']))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
       
        if(isset($_POST['deviceToken'])){
            $this->onAjax($user->setDevice($_POST['deviceToken'], $_POST['deviceType']));
        }else $this->corAjax();
    }
    
    public function actionTestPush() {
/*         //广播
        $title = '惊喜活动'; $text= '便民商旅举办整点积分活动, 整点下单有进行哦!';
        $params = array('behaviorType'=>'V000');
        
        $push = new Push(PUSH_IOS_KEY, PUSH_IOS_SECRET);
        $push->sendIOSBroadcast($text);
        
        $push = new Push(PUSH_ANDROID_KEY, PUSH_ANDROID_SECRET);
        $push->sendAndroidBroadcast($title, $text); */
        
        //单播
        $userID = 5;
        $orderID = 5;
        $title = '订单提示'; $text= "尊敬的客户您好, 恭喜您的订单{$orderID}出票成功, 点击查看详情";
        $params = array('behaviorType'=>'V001', 'orderID'=>$orderID);
        
        $user = User::model()->findByPk($userID);
        switch ($user->deviceType) {
            case 1:
                $push = new Push(PUSH_IOS_KEY, PUSH_IOS_SECRET);
                $push->sendIOSUnicast($text, $user->deviceToken, $params);
                break;
            
           case 2:
               $push = new Push(PUSH_ANDROID_KEY, PUSH_ANDROID_SECRET);
                $push->sendAndroidUnicast($title, $text, $user->deviceToken, $params);
                break;
        }     
    }
    
}