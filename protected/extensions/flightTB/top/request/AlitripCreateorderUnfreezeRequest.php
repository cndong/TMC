<?php
/**
 * TOP API: taobao.alitrip.createorder.unfreeze request
 * 
 * @author auto create
 * @since 1.0, 2016.04.19
 */
class AlitripCreateorderUnfreezeRequest
{
	
	private $apiParas = array();
	
	public function getApiMethodName()
	{
		return "taobao.alitrip.createorder.unfreeze";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
