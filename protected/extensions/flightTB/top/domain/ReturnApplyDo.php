<?php

/**
 * 退订单列表返回对象
 * @author auto create
 */
class ReturnApplyDo
{
	
	/** 
	 * 航线二字码
	 **/
	public $airline_code;
	
	/** 
	 * 退票提交时间
	 **/
	public $apply_time;
	
	/** 
	 * 到达机场三字码
	 **/
	public $arr_airport_code;
	
	/** 
	 * 舱位
	 **/
	public $cabin;
	
	/** 
	 * 出发机场三字码
	 **/
	public $dep_airport_code;
	
	/** 
	 * 到达时间
	 **/
	public $dep_time;
	
	/** 
	 * 退票成功时间
	 **/
	public $first_process_time;
	
	/** 
	 * 航班号
	 **/
	public $flight_no;
	
	/** 
	 * 数据项id
	 **/
	public $id;
	
	/** 
	 * 
	 **/
	public $is_voluntary;
	
	/** 
	 * 订单号
	 **/
	public $order_id;
	
	/** 
	 * 乘机人姓名
	 **/
	public $passenger_name;
	
	/** 
	 * 退款成功时间
	 **/
	public $pay_success_time;
	
	/** 
	 * 退票原因
	 **/
	public $reason_content;
	
	/** 
	 * 退票手续费（单位：元）
	 **/
	public $refund_fee;
	
	/** 
	 * 退款金额（单位：元）
	 **/
	public $refund_money;
	
	/** 
	 * 
	 **/
	public $refund_reason;
	
	/** 
	 * 退票状态，1：初始，2：接受，3：确认，4：失败，5：买家处理，6：卖家处理，7：等待小二回填，8：退款成功
	 **/
	public $status;
	
	/** 
	 * 票号
	 **/
	public $ticket_no;	
}
?>