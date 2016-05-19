<?php

/**
 * PNR对象（传入该对象将使用该PNR创建订单并出票，PNR校验无效时仍将重回换编出票流程）
 * @author auto create
 */
class PnrDo
{
	
	/** 
	 * 该PNR对应的乘机人证件号码（同乘机人信息中docId保持一致）
	 **/
	public $list_passenger_doc_id;
	
	/** 
	 * 创建PNR的office号
	 **/
	public $office_code;
	
	/** 
	 * PNR编码
	 **/
	public $pnr_code;	
}
?>