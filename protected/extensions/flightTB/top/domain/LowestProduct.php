<?php

/**
 * 低价产品信息
 * @author auto create
 */
class LowestProduct
{
	
	/** 
	 * 产品卖家id
	 **/
	public $agent_id;
	
	/** 
	 * 剩余座位数
	 **/
	public $amount;
	
	/** 
	 * 舱位
	 **/
	public $cabin;
	
	/** 
	 * 舱位等级
	 **/
	public $cabin_class;
	
	/** 
	 * 舱位价格（销售价，在下单接口传该价格进行下单）
	 **/
	public $cabin_price;
	
	/** 
	 * 优惠券信息
	 **/
	public $coupon_info;
	
	/** 
	 * 折扣
	 **/
	public $discount;
	
	/** 
	 * 错误码
	 **/
	public $error_code;
	
	/** 
	 * 错误信息
	 **/
	public $error_msg;
	
	/** 
	 * 机场建设费（单位：元）
	 **/
	public $fee;
	
	/** 
	 * 退改签规定
	 **/
	public $fmt_tuigaiqian_info;
	
	/** 
	 * 产品类型，QW：全网最低价产品 JX：精选产品 JP：金牌产品 HS：航司产品 KUFEI：酷飞产品
	 **/
	public $product_type;
	
	/** 
	 * 查询结果
	 **/
	public $result;
	
	/** 
	 * 是否成功
	 **/
	public $success;
	
	/** 
	 * 燃油附加费（单位：元）
	 **/
	public $tax;
	
	/** 
	 * 票面价
	 **/
	public $ticket_price;	
}
?>