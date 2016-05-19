<?php
/**
 * TOP API: taobao.alitrip.refundticket.calculate request
 * 
 * @author auto create
 * @since 1.0, 2016.04.19
 */
class AlitripRefundticketCalculateRequest
{
	/** 
	 * 接入方提供的用户名
	 **/
	private $channelName;
	
	/** 
	 * 淘宝订单号
	 **/
	private $orderId;
	
	/** 
	 * 接入方提供的密码，以MD5方式加密后传入
	 **/
	private $password;
	
	/** 
	 * Voluntary:自愿，NonVoluntary:非自愿
	 **/
	private $refundTicketType;
	
	/** 
	 * 需退票的票号，不填写则认为该订单所有票均需要退
	 **/
	private $ticketNumbers;
	
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

	public function setRefundTicketType($refundTicketType)
	{
		$this->refundTicketType = $refundTicketType;
		$this->apiParas["refund_ticket_type"] = $refundTicketType;
	}

	public function getRefundTicketType()
	{
		return $this->refundTicketType;
	}

	public function setTicketNumbers($ticketNumbers)
	{
		$this->ticketNumbers = $ticketNumbers;
		$this->apiParas["ticket_numbers"] = $ticketNumbers;
	}

	public function getTicketNumbers()
	{
		return $this->ticketNumbers;
	}

	public function getApiMethodName()
	{
		return "taobao.alitrip.refundticket.calculate";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->channelName,"channelName");
		RequestCheckUtil::checkNotNull($this->orderId,"orderId");
		RequestCheckUtil::checkNotNull($this->password,"password");
		RequestCheckUtil::checkNotNull($this->refundTicketType,"refundTicketType");
		RequestCheckUtil::checkMaxListSize($this->ticketNumbers,20,"ticketNumbers");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
