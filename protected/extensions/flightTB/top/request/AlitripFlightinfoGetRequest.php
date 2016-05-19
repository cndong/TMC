<?php
/**
 * TOP API: taobao.alitrip.flightinfo.get request
 * 
 * @author auto create
 * @since 1.0, 2016.04.19
 */
class AlitripFlightinfoGetRequest
{
	/** 
	 * 航空公司大写二字码
	 **/
	private $airlineCode;
	
	/** 
	 * 订单所用政策是否源自去啊，1是，0否
	 **/
	private $alitripFlag;
	
	/** 
	 * 到达机场三字码
	 **/
	private $arrAirport;
	
	/** 
	 * 乘客信息对象（请在询价时传入该对象，否则下单时可能遇到变价问题）
	 **/
	private $bookTravelerList;
	
	/** 
	 * 舱位
	 **/
	private $cabin;
	
	/** 
	 * 接入方提供的用户名
	 **/
	private $channelName;
	
	/** 
	 * 起飞机场三字码
	 **/
	private $depAirport;
	
	/** 
	 * 起飞日期
	 **/
	private $depDate;
	
	/** 
	 * 航班号
	 **/
	private $flightNo;
	
	/** 
	 * 是否已有PNR（是，则代表不再做AV座位数校验，下单时请传PNR相关信息）
	 **/
	private $hasPnr;
	
	/** 
	 * 询价时屏蔽的店铺名称（去啊上显示的店铺名称），支持多个
	 **/
	private $ignoredShopNames;
	
	/** 
	 * 询价时仅询价的店铺名称（需填写完整的店铺昵称或名称），支持多个
	 **/
	private $onlyShopNames;
	
	/** 
	 * 接入方提供的密码
	 **/
	private $password;
	
	/** 
	 * 订单收单价格（供后续阿里旅行做价格分析使用）
	 **/
	private $price;
	
	/** 
	 * 外部订单号（请传入同下单时一致的外部订单号，便于淘宝跟踪下单失败问题）
	 **/
	private $reservationCode;
	
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

	public function setAlitripFlag($alitripFlag)
	{
		$this->alitripFlag = $alitripFlag;
		$this->apiParas["alitrip_flag"] = $alitripFlag;
	}

	public function getAlitripFlag()
	{
		return $this->alitripFlag;
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

	public function setBookTravelerList($bookTravelerList)
	{
		$this->bookTravelerList = $bookTravelerList;
		$this->apiParas["book_traveler_list"] = $bookTravelerList;
	}

	public function getBookTravelerList()
	{
		return $this->bookTravelerList;
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

	public function setHasPnr($hasPnr)
	{
		$this->hasPnr = $hasPnr;
		$this->apiParas["has_pnr"] = $hasPnr;
	}

	public function getHasPnr()
	{
		return $this->hasPnr;
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

	public function setPrice($price)
	{
		$this->price = $price;
		$this->apiParas["price"] = $price;
	}

	public function getPrice()
	{
		return $this->price;
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
		return "taobao.alitrip.flightinfo.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->airlineCode,"airlineCode");
		RequestCheckUtil::checkNotNull($this->arrAirport,"arrAirport");
		RequestCheckUtil::checkNotNull($this->channelName,"channelName");
		RequestCheckUtil::checkNotNull($this->depAirport,"depAirport");
		RequestCheckUtil::checkNotNull($this->depDate,"depDate");
		RequestCheckUtil::checkNotNull($this->flightNo,"flightNo");
		RequestCheckUtil::checkMaxListSize($this->ignoredShopNames,50,"ignoredShopNames");
		RequestCheckUtil::checkMaxListSize($this->onlyShopNames,20,"onlyShopNames");
		RequestCheckUtil::checkNotNull($this->password,"password");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
