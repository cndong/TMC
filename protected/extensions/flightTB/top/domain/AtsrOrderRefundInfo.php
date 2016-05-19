<?php

/**
 * 退票信息对象
 * @author auto create
 */
class AtsrOrderRefundInfo
{
	
	/** 
	 * 极速退款方式，0：非极速退款，1：实时退款，2：一小时退款
	 **/
	public $instant_refund_type;
	
	/** 
	 * 乘客姓名
	 **/
	public $passenger_name;
	
	/** 
	 * 退票手续费（单位：元）
	 **/
	public $refund_fee;
	
	/** 
	 * 退款手续费（单位：分）
	 **/
	public $refund_fee_fen;
	
	/** 
	 * 退票航段
	 **/
	public $refund_segment;
	
	/** 
	 * 退票状态 1：退票处理中 2：退票成功 3：退票失败
	 **/
	public $refund_status;
	
	/** 
	 * 退款类型 1：自愿2：非自愿
	 **/
	public $refund_type;
	
	/** 
	 * 票号
	 **/
	public $ticket_no;	
}
?>