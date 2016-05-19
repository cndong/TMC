<?php
/**
 * TOP API: taobao.alitrip.orderinfo.pay request
 * 
 * @author auto create
 * @since 1.0, 2016.05.09
 */
class AlitripOrderinfoPayRequest
{
	/** 
	 * 接入方提供的用户名
	 **/
	private $channelName;
	
	/** 
	 * 外部订单号
	 **/
	private $outOrderId;
	
	/** 
	 * 接入方提供的密码
	 **/
	private $password;
	
	/** 
	 * 淘宝订单号
	 **/
	private $tbOrderId;
	
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

	public function setOutOrderId($outOrderId)
	{
		$this->outOrderId = $outOrderId;
		$this->apiParas["out_order_id"] = $outOrderId;
	}

	public function getOutOrderId()
	{
		return $this->outOrderId;
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

	public function setTbOrderId($tbOrderId)
	{
		$this->tbOrderId = $tbOrderId;
		$this->apiParas["tb_order_id"] = $tbOrderId;
	}

	public function getTbOrderId()
	{
		return $this->tbOrderId;
	}

	public function getApiMethodName()
	{
		return "taobao.alitrip.orderinfo.pay";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->channelName,"channelName");
		RequestCheckUtil::checkNotNull($this->outOrderId,"outOrderId");
		RequestCheckUtil::checkNotNull($this->password,"password");
		RequestCheckUtil::checkNotNull($this->tbOrderId,"tbOrderId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
