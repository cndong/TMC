<?php
/**
 * TOP API: taobao.alitrip.orderlist.fetch request
 * 
 * @author auto create
 * @since 1.0, 2016.04.19
 */
class AlitripOrderlistFetchRequest
{
	/** 
	 * 提取的订单日期
	 **/
	private $fetchDate;
	
	/** 
	 * 提取订单号大于该id的后续订单（该值请使用本接口返回的上一批数据中的最后一个订单id回填过来）
	 **/
	private $startOrderId;
	
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

	public function setStartOrderId($startOrderId)
	{
		$this->startOrderId = $startOrderId;
		$this->apiParas["start_order_id"] = $startOrderId;
	}

	public function getStartOrderId()
	{
		return $this->startOrderId;
	}

	public function getApiMethodName()
	{
		return "taobao.alitrip.orderlist.fetch";
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
