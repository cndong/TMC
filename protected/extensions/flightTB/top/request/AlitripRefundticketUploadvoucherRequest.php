<?php
/**
 * TOP API: taobao.alitrip.refundticket.uploadvoucher request
 * 
 * @author auto create
 * @since 1.0, 2016.04.19
 */
class AlitripRefundticketUploadvoucherRequest
{
	/** 
	 * 接入方提供的用户名
	 **/
	private $channelName;
	
	/** 
	 * 退票凭证文件体
	 **/
	private $fileContent;
	
	/** 
	 * 该附件对应的订单号
	 **/
	private $orderId;
	
	/** 
	 * 接入方提供的密码，以MD5方式加密后传入
	 **/
	private $password;
	
	private $apiParas = array();
	
	public function setChannelName($channelName)
	{
		$this->channelName = $channelName;
		$this->apiParas["channel_name"] = $channelName;
	}

	public function getChannelName()
	{
		return $this->channelName;
	}

	public function setFileContent($fileContent)
	{
		$this->fileContent = $fileContent;
		$this->apiParas["file_content"] = $fileContent;
	}

	public function getFileContent()
	{
		return $this->fileContent;
	}

	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
		$this->apiParas["order_id"] = $orderId;
	}

	public function getOrderId()
	{
		return $this->orderId;
	}

	public function setPassword($password)
	{
		$this->password = $password;
		$this->apiParas["password"] = $password;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getApiMethodName()
	{
		return "taobao.alitrip.refundticket.uploadvoucher";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->channelName,"channelName");
		RequestCheckUtil::checkNotNull($this->fileContent,"fileContent");
		RequestCheckUtil::checkNotNull($this->orderId,"orderId");
		RequestCheckUtil::checkNotNull($this->password,"password");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
