<?php

/**
 * 乘客信息
 * @author auto create
 */
class AtsrOrderTravelerInfo
{
	
	/** 
	 * 出生日期
	 **/
	public $birth_date;
	
	/** 
	 * 证件号码
	 **/
	public $doc_id;
	
	/** 
	 * 证件类型0，身份证；1，护照；3，军人证；4，回乡证；5，台胞证；6，港澳台胞；10，警官证；11，士兵证件
	 **/
	public $doc_type;
	
	/** 
	 * 旅客姓名
	 **/
	public $passenger_name;
	
	/** 
	 * 旅客类型ADT:成人 CHD：儿童
	 **/
	public $passenger_type;
	
	/** 
	 * 票号
	 **/
	public $ticket_no;	
}
?>