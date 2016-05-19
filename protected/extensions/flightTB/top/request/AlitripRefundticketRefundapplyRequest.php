<?php
/**
 * TOP API: taobao.alitrip.refundticket.refundapply request
 * 
 * @author auto create
 * @since 1.0, 2016.04.19
 */
class AlitripRefundticketRefundapplyRequest
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
	 * 退票手续费（单位：元，非自愿退票及无法计算退票费时可不填）
	 **/
	private $refundFee;
	
	/** 
	 * 1：自愿退票（我要改变行程计划、我不想飞）；31：自愿退票（填错名字、选错日期、选错航班）；32：自愿退票（没赶上飞机、证件忘带了）；33：自愿退票（生病了无法乘机（无二甲医院证明））；99：自愿退票（其他）；6：非自愿退票（航班延误或取消、航班时刻变更等航司原因）；21：非自愿退票（身体原因且有二级甲等医院<含>以上的医院证明）；
	 **/
	private $refundReasonType;
	
	/** 
	 * 退票原因说明（非自愿退票必填）
	 **/
	private $refundTicketDetail;
	
	/** 
	 * Voluntary:自愿，NonVoluntary:非自愿
	 **/
	private $refundTicketType;
	
	/** 
	 * 需退票的票号，一个订单仅有1个乘机人时无须填写（部分航司同时仅支持1个票号提交退票）
	 **/
	private $ticketNumbers;
	
	/** 
	 * 通过凭证上传接口上传的凭证信息
	 **/
	private $voucherInfos;
	
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

	public function setRefundFee($refundFee)
	{
		$this->refundFee = $refundFee;
		$this->apiParas["refund_fee"] = $refundFee;
	}

	public function getRefundFee()
	{
		return $this->refundFee;
	}

	public function setRefundReasonType($refundReasonType)
	{
		$this->refundReasonType = $refundReasonType;
		$this->apiParas["refund_reason_type"] = $refundReasonType;
	}

	public function getRefundReasonType()
	{
		return $this->refundReasonType;
	}

	public function setRefundTicketDetail($refundTicketDetail)
	{
		$this->refundTicketDetail = $refundTicketDetail;
		$this->apiParas["refund_ticket_detail"] = $refundTicketDetail;
	}

	public function getRefundTicketDetail()
	{
		return $this->refundTicketDetail;
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

	public function setVoucherInfos($voucherInfos)
	{
		$this->voucherInfos = $voucherInfos;
		$this->apiParas["voucher_infos"] = $voucherInfos;
	}

	public function getVoucherInfos()
	{
		return $this->voucherInfos;
	}

	public function getApiMethodName()
	{
		return "taobao.alitrip.refundticket.refundapply";
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
