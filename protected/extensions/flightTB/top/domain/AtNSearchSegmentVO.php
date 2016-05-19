<?php

/**
 * 国内机票搜索航段信息
 * @author auto create
 */
class AtNSearchSegmentVO
{
	
	/** 
	 * 活动优惠金额,单位分。
	 **/
	public $activity_bonus;
	
	/** 
	 * 活动编号
	 **/
	public $activity_id;
	
	/** 
	 * 到达城市三字码
	 **/
	public $arr_city;
	
	/** 
	 * 是否自动出票。
	 **/
	public $auto_book;
	
	/** 
	 * 基准舱位（FCY）价格，单位分
	 **/
	public $basic_cabin_price;
	
	/** 
	 * 舱位代码
	 **/
	public $cabin;
	
	/** 
	 * 舱位等级。0-头等舱；1-商务舱；2-经济舱
	 **/
	public $cabin_class;
	
	/** 
	 * 舱位座位数，123456789AQSCL等。
	 **/
	public $cabin_num;
	
	/** 
	 * 出发城市三字码
	 **/
	public $dep_city;
	
	/** 
	 * 运价类型。0-公布运价；1-折扣运价；2-B2B/卖家接口政策；3-航司外部产品
	 **/
	public $fare_type;
	
	/** 
	 * 产品打标，2的N次方标记。
	 **/
	public $flag;
	
	/** 
	 * 航班号
	 **/
	public $flight_no;
	
	/** 
	 * 是否为强制保险
	 **/
	public $force_insure;
	
	/** 
	 * 强制保险金额，单位分。
	 **/
	public $force_insure_price;
	
	/** 
	 * 发票提供类型。1-提供（等额行程单）；2-不提供；5-提供（等额行程单+发票）；6-提供（等额发票）
	 **/
	public $invoice_type;
	
	/** 
	 * 销售价格，单位分。
	 **/
	public $price;
	
	/** 
	 * 航段标识。00-去程第一段；01-去程第二段；10-回程第一段；11-回程第二段。
	 **/
	public $segment_number;
	
	/** 
	 * 库存类型，0-共有库存；1-私有库存；2-可申请库存
	 **/
	public $stock_type;
	
	/** 
	 * 产品票面价，单位分
	 **/
	public $ticket_price;	
}
?>