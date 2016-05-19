<?php
/**
 * TOP API: taobao.alitrip.orderinfo.airbook request
 * 
 * @author auto create
 * @since 1.0, 2016.05.09
 */
class AlitripOrderinfoAirbookRequest
{
	/** 
	 * 联系人信息对象
	 **/
	private $bookArrangerInfo;
	
	/** 
	 * 航班信息
	 **/
	private $bookFlightSegmentList;
	
	/** 
	 * 乘客信息
	 **/
	private $bookTravelerList;
	
	/** 
	 * 接入方提供的用户名
	 **/
	private $channelName;
	
	/** 
	 * 扩展字段
	 **/
	private $extra;
	
	/** 
	 * 机场建设费（单位：元，按单人填写）
	 **/
	private $fee;
	
	/** 
	 * 下单时屏蔽的店铺名称（需填写完整的店铺昵称），支持多个
	 **/
	private $ignoredShopNames;
	
	/** 
	 * PNR对象（传入该对象将使用该PNR创建订单并出票，PNR校验无效时仍将重回换编出票流程）
	 **/
	private $listPnrDo;
	
	/** 
	 * 下单时仅购买的店铺名称（需填写完整的店铺昵称或名称），支持多个
	 **/
	private $onlyShopNames;
	
	/** 
	 * 接入方提供的密码，以32位MD5加密后传入，MD5后不区分大小写
	 **/
	private $password;
	
	/** 
	 * 下单时选择的产品类型，QW：全网最低价产品 JX：精选产品 JP：金牌产品 HS：航司产品 KUFEI：酷飞产品 默认HS，即：下单默认选择旗舰店产品
	 **/
	private $productType;
	
	/** 
	 * 退票险受益人信息（不购买退票险，请勿传入该参数）
	 **/
	private $refundInsuranceList;
	
	/** 
	 * 外部系统订单号
	 **/
	private $reservationCode;
	
	/** 
	 * 燃油附加费（单位：元，按单人填写）
	 **/
	private $tax;
	
	/** 
	 * 订单总价（单位：元）
	 **/
	private $totalMoney;
	
	private $apiParas = array();
	
	public function setBookArrangerInfo($bookArrangerInfo)
	{
		$this->bookArrangerInfo = $bookArrangerInfo;
		$this->apiParas["book_arranger_info"] = $bookArrangerInfo;
	}

	public function getBookArrangerInfo()
	{
		return $this->bookArrangerInfo;
	}

	public function setBookFlightSegmentList($bookFlightSegmentList)
	{
		$this->bookFlightSegmentList = $bookFlightSegmentList;
		$this->apiParas["book_flight_segment_list"] = $bookFlightSegmentList;
	}

	public function getBookFlightSegmentList()
	{
		return $this->bookFlightSegmentList;
	}

	public function setBookTravelerList($bookTravelerList)
	{
		$this->bookTravelerList = $bookTravelerList;
		$this->apiParas["book_traveler_list"] = $bookTravelerList;
	}

	public function getBookTravelerList()
	{
		return $this->bookTravelerList;
	}

	public function setChannelName($channelName)
	{
		$this->channelName = $channelName;
		$this->apiParas["channel_name"] = $channelName;
	}

	public function getChannelName()
	{
		return $this->channelName;
	}

	public function setExtra($extra)
	{
		$this->extra = $extra;
		$this->apiParas["extra"] = $extra;
	}

	public function getExtra()
	{
		return $this->extra;
	}

	public function setFee($fee)
	{
		$this->fee = $fee;
		$this->apiParas["fee"] = $fee;
	}

	public function getFee()
	{
		return $this->fee;
	}

	public function setIgnoredShopNames($ignoredShopNames)
	{
		$this->ignoredShopNames = $ignoredShopNames;
		$this->apiParas["ignored_shop_names"] = $ignoredShopNames;
	}

	public function getIgnoredShopNames()
	{
		return $this->ignoredShopNames;
	}

	public function setListPnrDo($listPnrDo)
	{
		$this->listPnrDo = $listPnrDo;
		$this->apiParas["list_pnr_do"] = $listPnrDo;
	}

	public function getListPnrDo()
	{
		return $this->listPnrDo;
	}

	public function setOnlyShopNames($onlyShopNames)
	{
		$this->onlyShopNames = $onlyShopNames;
		$this->apiParas["only_shop_names"] = $onlyShopNames;
	}

	public function getOnlyShopNames()
	{
		return $this->onlyShopNames;
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

	public function setProductType($productType)
	{
		$this->productType = $productType;
		$this->apiParas["product_type"] = $productType;
	}

	public function getProductType()
	{
		return $this->productType;
	}

	public function setRefundInsuranceList($refundInsuranceList)
	{
		$this->refundInsuranceList = $refundInsuranceList;
		$this->apiParas["refund_insurance_list"] = $refundInsuranceList;
	}

	public function getRefundInsuranceList()
	{
		return $this->refundInsuranceList;
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

	public function setTax($tax)
	{
		$this->tax = $tax;
		$this->apiParas["tax"] = $tax;
	}

	public function getTax()
	{
		return $this->tax;
	}

	public function setTotalMoney($totalMoney)
	{
		$this->totalMoney = $totalMoney;
		$this->apiParas["total_money"] = $totalMoney;
	}

	public function getTotalMoney()
	{
		return $this->totalMoney;
	}

	public function getApiMethodName()
	{
		return "taobao.alitrip.orderinfo.airbook";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->channelName,"channelName");
		RequestCheckUtil::checkNotNull($this->fee,"fee");
		RequestCheckUtil::checkMaxListSize($this->ignoredShopNames,200,"ignoredShopNames");
		RequestCheckUtil::checkMaxListSize($this->onlyShopNames,200,"onlyShopNames");
		RequestCheckUtil::checkNotNull($this->password,"password");
		RequestCheckUtil::checkNotNull($this->reservationCode,"reservationCode");
		RequestCheckUtil::checkNotNull($this->tax,"tax");
		RequestCheckUtil::checkNotNull($this->totalMoney,"totalMoney");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
