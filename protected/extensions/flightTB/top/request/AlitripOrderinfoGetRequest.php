<?php
/**
 * TOP API: taobao.alitrip.orderinfo.get request
 * 
 * @author auto create
 * @since 1.0, 2016.04.19
 */
class AlitripOrderinfoGetRequest
{
	/** 
	 * 接入方提供的用户名
	 **/
	private $channelName;
	
	/** 
	 * 阿里旅行订单号（该入参及外部订单号其中一个必填）
	 **/
	private $orderId;
	
	/** 
	 * 接入方提供的密码，以MD5方式加密后传入
	 **/
	private $password;
	
	/** 
	 * 外部订单号（该入参及阿里旅行订单号其中一个必填）
	 **/
	private $reservationCode;
	
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

	public function setReservationCode($reservationCode)
	{
		$this->reservationCode = $reservationCode;
		$this->apiParas["reservation_code"] = $reservationCode;
	}

	public function getReservationCode()
	{
		return $this->reservationCode;
	}

	public function getApiMethodName()
	{
		return "taobao.alitrip.orderinfo.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->channelName,"channelName");
		RequestCheckUtil::checkNotNull($this->password,"password");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
