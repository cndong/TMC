<?php

/**
 * 乘客信息
 * @author auto create
 */
class AtsrBookTravelerInfoDO
{
	
	/** 
	 * 出生日期（yyyy-MM-dd）
	 **/
	public $birth_date;
	
	/** 
	 * 证件号码
	 **/
	public $doc_id;
	
	/** 
	 * 证件类型 PP:护照 NI:身份证 MI:军人证 BH:回乡证 TW:台胞证 HK:港澳通行证 PO:警官证 SO:士兵证 TH:其他证件
	 **/
	public $doc_type;
	
	/** 
	 * 乘客姓名
	 **/
	public $passenger_name;
	
	/** 
	 * 乘客类型 ADT:成人 CHD：儿童
	 **/
	public $passenger_type;	
}
?>