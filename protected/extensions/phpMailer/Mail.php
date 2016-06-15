<?php 
/*
 * $param array $sendInfo = array(
 * 					'CharSet' => 'utf-8',		//可传，设置编码，默认utf-8
 * 					'Port' => '25',				//可传，设置SMTP主机端口号，默认25
 * 					'Host' => '25',				//可传，设置SMTP主机服务器，默认25
 * 					'Username' => '******@qq.com',		//可传，设置 SMTP服务器用户名（填写完整的Email地址）
 * 					'Password' => '******',				//可传，设置 SMTP服务器用户名
 * 					'From' => '******@qq.com',			//可传，设置发件人地址，默认用户名地址（填写完整的Email地址）
 * 					'FromName' => 'test',		//可传，设置发件人名称，默认为发件人地址@前半部分，或者用户名@前半部分
 * 					'To' => array(
 * 							[0]=>array('email'=>'onemail@qq.com','name'=>'onename'),
 * 							[1]=>array('email'=>'twomail@qq.com','name'=>'twoname'),
 * 							[2]=>array('email'=>'twomail@qq.com','name'=>'twoname'),
 * 						)					//可传，设置收件人邮箱和地址，二维数组形式，email必填，name可选
 * 					'AddBCC' => array(
 * 							[0]=>array('email'=>'onemail@qq.com','name'=>'onename'),
 * 							[1]=>array('email'=>'twomail@qq.com','name'=>'twoname'),
 * 							[2]=>array('email'=>'twomail@qq.com','name'=>'twoname'),
 * 						)					//可传，设置密送邮箱和地址，二维数组形式，email必填，name可选
 * 					'AddCC' => array(
 * 							[0]=>array('email'=>'onemail@qq.com','name'=>'onename'),
 * 							[1]=>array('email'=>'twomail@qq.com','name'=>'twoname'),
 * 							[2]=>array('email'=>'twomail@qq.com','name'=>'twoname'),
 * 						)					//可传，设置抄送邮箱和地址，二维数组形式，email必填，name可选
 * 					'AddAttachment' => array(
 * 									[0]=>array('attach'=>'D:/www/theone/protected/components/PHPMailer/360log.png','name'=>'360log.png'),
 * 								)		//可传，设置附件和附件名称，二维数组形式，attach必填，name可选
 * 					'Subject' => 'Test Mail',	//可传，设置邮件主题
 * 					'Body' => 'This is a test mail!',	//可传，设置邮件的内容
 * 					'AddReplyTo' => 'xxx@sina.com','xxxx',	//可传，设置回复地址
 * 					'typeInfo' => array(),	//可传，设置模板参数
 * 			)
 * $param string $tpl 设置邮件模板('cpl':十分便民火车票出票量日报表 )
 * return array $mailResult array('status'=>'success/fail','msg'=>'')
 * 调用方法：
 *  //模板版本
 *  Yii::import ('application.components.PHPMailer.Mail',true);
 *	$cpl['typeInfo']['orderID'] = 10052 ;
 *	$cpl['To'][0]['email'] = 'yangjing@sfbm.com' ;
 *	Mail::sendMail($cpl,'cpl');
 *	//非模板版本
 *  Yii::import ('application.components.PHPMailer.Mail',true);
 *	$cpl['Subject'] = 'Test' ;
 *	$cpl['Body'] = 'This Is An Test Mail' ;
 *	$cpl['To'][0]['email'] = 'yangjing@sfbm.com' ;
 *	$cpl['To'][1]['email'] = 'ningyangjing@126.com';
 *	Mail::sendMail($cpl); 
 */

class Mail{
	 public static function sendMail($sendInfo=array(),$tpl=''){
	    Q::log($sendInfo, 'mail.send.'.$tpl);
	    $mailResult = array();
	    if(!QEnv::IS_SEND_SMS) return $mailResult = array('status'=>'success','msg'=>'');
	 	if($tpl == ''){
	 		if(!isset($sendInfo['Subject']) || !isset($sendInfo['Body'])){
	 			$mailResult['status'] = 'fail';
				$mailResult['msg'] = "您未使用模板发送，请填写邮件主题和内容再发送！";
	 		}
	 	}else{
	 		if(!isset($sendInfo['tplInfo']) || empty($sendInfo['tplInfo'])){
	 			$mailResult['status'] = 'fail';
				$mailResult['msg'] = "您使用模板发送邮件，请输入参数！";
	 		}else{
	 			$resultTPL = Mail::getTPL($tpl,$sendInfo['tplInfo']);
	 			if(!empty($resultTPL)){
	 				$sendInfo['Subject'] = $resultTPL['Subject'];
	 				$sendInfo['Body'] = $resultTPL['Body'];
	 				if(isset($resultTPL['To'])) $sendInfo['To'] = $resultTPL['To'];
	 			}else{
	 				$mailResult['status'] = 'fail';
					$mailResult['msg'] = "调用模板失败，请输入完整的参数！";
	 			}
	 		}
	 	}
	 	if($mailResult) {
	 	    Q::log($mailResult, 'mail.mailResult.Error');
	 	    return $mailResult;
	 	}
	 	//实例化
		$mail = new PHPMailer();
		//赋予变量默认值
		if(!isset($sendInfo['Username'])){
			$sendInfo['Username'] = 'noreply@qumaiya.com';
		}
		$fromName = array();
		$fromName = explode("@",$sendInfo['Username']);
	 	if(!isset($sendInfo['Password'])){
			$sendInfo['Password'] = 'no2013';
		}
		if(!isset($sendInfo['CharSet'])){
			$sendInfo['CharSet'] = 'utf-8';
		}
		if(!isset($sendInfo['Port'])){
			$sendInfo['Port'] = 25;
		}
	 	if(!isset($sendInfo['Host'])){
/* 			$host = trim($fromName[1]);
			if($host == 'sfbm.com'){
				$host = 'qq.com';
			}else if($host == 'qumaiya.com'){
				$host = 'exmail.qq.com';
			} */
			$host = 'exmail.qq.com';
			$sendInfo['Host'] = "smtp.".$host;
		}
		if(!isset($sendInfo['From'])){
			$sendInfo['From'] = $sendInfo['Username'];
		}
		if(!isset($sendInfo['FromName'])){
			$fromMail = array();
			$fromMail = explode("@",$sendInfo['From']);
			$sendInfo['FromName'] = $fromMail[0];
		}
		
		//是否通过SMTP协议发送
		$mail -> ISSMTP();
		//SMTP服务器是否需要验证(验证为true 不验证为false)
		$mail -> SMTPAuth = true;
		//设置用户名
		$mail -> Username = $sendInfo['Username'];
		//设置密码
		$mail -> Password = $sendInfo['Password'];
		//设置编码
		$mail -> CharSet = $sendInfo['CharSet'];
		//设置端口
		$mail -> Port = $sendInfo['Port'];
		//设置主机服务器
		$mail -> Host = $sendInfo['Host'];	
		//发件人地址
		$mail -> From = $sendInfo['From'];
		//发件人
		$mail -> FromName = $sendInfo['Username'];
		//是否以HTML格式发送
		$mail -> IsHTML(true);
		//主题
		$mail -> Subject = $sendInfo['Subject'];
		//内容
		$mail -> Body = $sendInfo['Body'];
	 	//添加附件
		if(isset($sendInfo['AddAttachment'])){
			if(is_array($sendInfo['AddAttachment'])){
				foreach($sendInfo['AddAttachment'] as $attach){
					if(isset($attach['name'])){
						$mail -> AddAttachment($attach['attach'],$attach['name']);
					}else{
						$mail -> AddAttachment($attach['attach']);
					}
				}
			}
		}
		 //调用回复方法,添加回复对象
		if(isset($sendInfo['AddReplyTo'])){
			$mail -> AddReplyTo($sendInfo['AddReplyTo']); 
		}
		//添加收件人，支持群发
		$success = true;
		if(isset($sendInfo['To'])){
			foreach($sendInfo['To'] as $addr){
				if(isset($addr['name'])){
					$mail -> AddAddress($addr['email'],$addr['name']);
				}else{
					$email = array();
					$email = explode("@",$addr['email']);
					$mail -> AddAddress($addr['email'],$email[0]);
				}
			}
		}
		//密送
		if(isset($sendInfo['AddBCC'])){
			foreach($sendInfo['AddBCC'] as $addrB){
				if(isset($addrB['name'])){
					$mail -> AddBCC($addrB['email'],$addrB['name']);
				}else{
					$email = array();
					$email = explode("@",$addrB['email']);
					$mail -> AddBCC($addrB['email'],$email[0]);
				}
			}
		}
		//抄送
		if(isset($sendInfo['AddCC'])){ 
			foreach($sendInfo['AddCC'] as $addrC){
				if(isset($addrC['name'])){
					$mail -> AddCC($addrC['email'],$addrC['name']);
				}else{
					$email = array();
					$email = explode("@",$addrC['email']);
					$mail -> AddCC($addrC['email'],$email[0]);
				}
			}

		}
		$error = array();
		if (!$mail->Send()){
			$success = false;
			$error[] = "发送失败，原因：".$mail->ErrorInfo;
        }
		$mail -> ClearAddresses();
		if(!isset($sendInfo['To']) && !isset($sendInfo['AddCC']) && !isset($sendInfo['AddBCC']) ){ 
			$mail -> AddAddress('wangbendong@sfbm.com','wangbendong');
			if (!$mail->Send()){
				$success = false;
				$error[] = "向wangbendong@sfbm.com发送失败，原因：".$mail->ErrorInfo;
            }
		}
		$mailResult = array();
		if($success){
			$mailResult['status'] = 'success';
			$mailResult['msg'] = '发送邮件成功！';
		}else{
			$mailResult['status'] = 'fail';
			if(!empty($error)){
				$mailResult['msg'] = $error;
			}else{
				$mailResult['msg'] = '发送邮件失败！';
			}
		}
	    Q::log($mailResult, 'mail.mailResult');
		return $mailResult;
	}
	
	public static function getTPL($tpl,$content){
        $result = array();
        $body = "订单: <a href='http://tmc.qumaipiao.com/boss/flight/orderList/orderID/{$content['orderID']}'>{$content['orderID']}</a>";
        $email = 'g-flight@sfbm.com'; 
        switch($tpl){
            case 'CheckSucc':
                    $result['Subject'] = "新订单-对公-审核通过-{$content['orderID']}";
                    $result['Body'] = $body;
                    $result['To'][0]['email'] = $email;
                    break;
            case 'Payed':
                    $result['Subject'] = "新订单-对私-已支付-{$content['orderID']}";
                    $result['Body'] = $body;
                    $result['To'][0]['email'] = $email;
                    break;
          case 'ApplyRfd':
                    $result['Subject'] = "退票-{$content['orderID']}";
                    $result['Body'] = $body;
                    $result['To'][0]['email'] = $email;
                    break;
          case 'ApplyRsn':
                    $result['Subject'] = "改签-{$content['orderID']}";
                    $result['Body'] = $body;
                    $result['To'][0]['email'] = $email;
                    break;
        }
        return $result;
	}
}
?>