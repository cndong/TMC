<?php

/**
 * 订单对象
 * @author auto create
 */
class AtsrOrderInfoResultDo
{
	
	/** 
	 * 实际支付金额（单位：元，后续提供该字段的输出）
	 **/
	public $actual_pay;
	
	/** 
	 * 支付宝交易流水号
	 **/
	public $alipay_trade_no;
	
	/** 
	 * 错误码
	 **/
	public $error_code;
	
	/** 
	 * 错误信息
	 **/
	public $error_msg;
	
	/** 
	 * 航班信息列表
	 **/
	public $flight_info_list;
	
	/** 
	 * 订单创建时间
	 **/
	public $gmt_create;
	
	/** 
	 * 是否金牌产品
	 **/
	public $is_quality_order;
	
	/** 
	 * 是否使用了优惠券
	 **/
	public $is_voucher_order;
	
	/** 
	 * 订单id
	 **/
	public $order_id;
	
	/** 
	 * 支付时间
	 **/
	public $pay_time;
	
	/** 
	 * QW：全网最低价产品 JX：精选产品 JP：金牌产品 HS：航司产品 KUFEI：酷飞产品 默认HS，即：下单默认选择旗舰店产品
	 **/
	public $product_type;
	
	/** 
	 * 外部订单号
	 **/
	public $reservation_code;
	
	/** 
	 * 卖家淘宝昵称
	 **/
	public $seller_taobao_nick;
	
	/** 
	 * 是否精选产品
	 **/
	public $speedy_order;
	
	/** 
	 * 订单状态
	 **/
	public $status;
	
	/** 
	 * 查询结果是否成功
	 **/
	public $success;
	
	/** 
	 * 订单总金额（单位：元，优惠前总金额）
	 **/
	public $total_price;
	
	/** 
	 * 乘机人列表
	 **/
	public $traveler_info_list;	
}
?>