<?php

/**
 * 航班信息
 * @author auto create
 */
class AtsrBookFlightSegmentDO
{
	
	/** 
	 * 航空公司大写二字码
	 **/
	public $airline_code;
	
	/** 
	 * 到达机场三字码
	 **/
	public $arr_airport;
	
	/** 
	 * 到达时间（HH:mm） 24小时制
	 **/
	public $arr_time;
	
	/** 
	 * 舱位
	 **/
	public $cabin;
	
	/** 
	 * 出发机场三字码
	 **/
	public $dep_airport;
	
	/** 
	 * 出发日期
	 **/
	public $dep_date;
	
	/** 
	 * 起飞时间（HH:mm） 24小时制
	 **/
	public $dep_time;
	
	/** 
	 * 航班号
	 **/
	public $flight_no;
	
	/** 
	 * 0：去程  1：回程
	 **/
	public $leg;
	
	/** 
	 * 退票险价格（通过退票险询价接口获取）
	 **/
	public $refund_insurance_price;
	
	/** 
	 * 销售价(单位：元)
	 **/
	public $sale_price;	
}
?>