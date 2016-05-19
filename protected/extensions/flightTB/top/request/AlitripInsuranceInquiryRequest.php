<?php
/**
 * TOP API: taobao.alitrip.insurance.inquiry request
 * 
 * @author auto create
 * @since 1.0, 2016.04.19
 */
class AlitripInsuranceInquiryRequest
{
	/** 
	 * 航司二字码
	 **/
	private $airlineCode;
	
	/** 
	 * 到达机场三字码
	 **/
	private $arrAirport;
	
	/** 
	 * 舱位
	 **/
	private $cabin;
	
	/** 
	 * 接入方提供的用户名
	 **/
	private $channelName;
	
	/** 
	 * 出发机场三字码
	 **/
	private $depAirport;
	
	/** 
	 * 出发日期
	 **/
	private $depDate;
	
	/** 
	 * 航班号
	 **/
	private $flightNo;
	
	/** 
	 * 屏蔽的店铺名称
	 **/
	private $ignoredShopNames;
	
	/** 
	 * 接入方提供的密码，以MD5方式加密后传入
	 **/
	private $password;
	
	/** 
	 * 保险询价时选择的产品类型，QW：全网最低价产品 JX：精选产品 JP：金牌产品 HS：航司产品 默认QW，即：默认选择全网最低价产品
	 **/
	private $productType;
	
	/** 
	 * 外部订单号
	 **/
	private $reservationCode;
	
	/** 
	 * 销售价格
	 **/
	private $salePrice;
	
	/** 
	 * 旅行人数
	 **/
	private $traverNumber;
	
	private $apiParas = array();
	
	public function setAirlineCode($airlineCode)
	{
		$this->airlineCode = $airlineCode;
		$this->apiParas["airline_code"] = $airlineCode;
	}

	public function getAirlineCode()
	{
		return $this->airlineCode;
	}

	public function setArrAirport($arrAirport)
	{
		$this->arrAirport = $arrAirport;
		$this->apiParas["arr_airport"] = $arrAirport;
	}

	public function getArrAirport()
	{
		return $this->arrAirport;
	}

	public function setCabin($cabin)
	{
		$this->cabin = $cabin;
		$this->apiParas["cabin"] = $cabin;
	}

	public function getCabin()
	{
		return $this->cabin;
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

	public function setDepAirport($depAirport)
	{
		$this->depAirport = $depAirport;
		$this->apiParas["dep_airport"] = $depAirport;
	}

	public function getDepAirport()
	{
		return $this->depAirport;
	}

	public function setDepDate($depDate)
	{
		$this->depDate = $depDate;
		$this->apiParas["dep_date"] = $depDate;
	}

	public function getDepDate()
	{
		return $this->depDate;
	}

	public function setFlightNo($flightNo)
	{
		$this->flightNo = $flightNo;
		$this->apiParas["flight_no"] = $flightNo;
	}

	public function getFlightNo()
	{
		return $this->flightNo;
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

	public function setReservationCode($reservationCode)
	{
		$this->reservationCode = $reservationCode;
		$this->apiParas["reservation_code"] = $reservationCode;
	}

	public function getReservationCode()
	{
		return $this->reservationCode;
	}

	public function setSalePrice($salePrice)
	{
		$this->salePrice = $salePrice;
		$this->apiParas["sale_price"] = $salePrice;
	}

	public function getSalePrice()
	{
		return $this->salePrice;
	}

	public function setTraverNumber($traverNumber)
	{
		$this->traverNumber = $traverNumber;
		$this->apiParas["traver_number"] = $traverNumber;
	}

	public function getTraverNumber()
	{
		return $this->traverNumber;
	}

	public function getApiMethodName()
	{
		return "taobao.alitrip.insurance.inquiry";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->airlineCode,"airlineCode");
		RequestCheckUtil::checkNotNull($this->arrAirport,"arrAirport");
		RequestCheckUtil::checkNotNull($this->cabin,"cabin");
		RequestCheckUtil::checkNotNull($this->channelName,"channelName");
		RequestCheckUtil::checkNotNull($this->depAirport,"depAirport");
		RequestCheckUtil::checkNotNull($this->depDate,"depDate");
		RequestCheckUtil::checkNotNull($this->flightNo,"flightNo");
		RequestCheckUtil::checkMaxListSize($this->ignoredShopNames,20,"ignoredShopNames");
		RequestCheckUtil::checkNotNull($this->password,"password");
		RequestCheckUtil::checkNotNull($this->reservationCode,"reservationCode");
		RequestCheckUtil::checkNotNull($this->salePrice,"salePrice");
		RequestCheckUtil::checkNotNull($this->traverNumber,"traverNumber");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
