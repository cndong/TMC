<?php

/**
 * 航班信息
 * @author auto create
 */
class AtsrOrderFlightInfo
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
	 * 到达时间（yyyy-MM-dd HH:mm） 24小时制
	 **/
	public $arr_time;
	
	/** 
	 * 机场建设费（单位：元）
	 **/
	public $build_price;
	
	/** 
	 * 仓位
	 **/
	public $cabin;
	
	/** 
	 * 出发机场三字码
	 **/
	public $dep_airport;
	
	/** 
	 * 起飞日期(yyyy-MM-dd)
	 **/
	public $dep_date;
	
	/** 
	 * 起飞时间（yyyy-MM-dd HH:mm） 24小时制
	 **/
	public $dep_time;
	
	/** 
	 * 航班号
	 **/
	public $flight_no;
	
	/** 
	 * 燃油附加费（单位：元）
	 **/
	public $oil_price;
	
	/** 
	 * 销售价(单位：元)
	 **/
	public $sale_price;
	
	/** 
	 * 行程类型 0：去程 1：回程
	 **/
	public $segment_type;	
}
?>