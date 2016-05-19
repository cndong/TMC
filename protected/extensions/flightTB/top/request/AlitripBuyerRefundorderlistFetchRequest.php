<?php
/**
 * TOP API: taobao.alitrip.buyer.refundorderlist.fetch request
 * 
 * @author auto create
 * @since 1.0, 2016.04.19
 */
class AlitripBuyerRefundorderlistFetchRequest
{
	/** 
	 * 提取数据日期（默认提取1天内的数据）
	 **/
	private $fetchDate;
	
	private $apiParas = array();
	
	public function setFetchDate($fetchDate)
	{
		$this->fetchDate = $fetchDate;
		$this->apiParas["fetch_date"] = $fetchDate;
	}

	public function getFetchDate()
	{
		return $this->fetchDate;
	}

	public function getApiMethodName()
	{
		return "taobao.alitrip.buyer.refundorderlist.fetch";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->fetchDate,"fetchDate");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
