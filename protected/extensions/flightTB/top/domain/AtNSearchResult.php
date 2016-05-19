<?php

/**
 * 国内机票搜索结果
 * @author auto create
 */
class AtNSearchResult
{
	
	/** 
	 * json串，描述活动信息。type：1普通直减、2后返、3多人同行
	 **/
	public $activity_map_json;
	
	/** 
	 * json格式字符串：描述代理商的基本信息结构；注意：仅在指定航班号查询的时候有输出。得分项可能有三个Key（不一定全部输出）: normalProdcutScore,specialProductScore,speedIssueProductScore，分别表示普通产品得分，特殊产品（申请票）得分，极速出票产品得分。
supplyType: 1 ：航空公司代理商， 2： 普通机票代理商
	 **/
	public $agent_info_map;
	
	/** 
	 * JSON串，描述航空公司二字码与中文名称的关系
	 **/
	public $airline_info_map;
	
	/** 
	 * JSON串，描述机场三字码和中文名称的对应关系
	 **/
	public $airport_info_map;
	
	/** 
	 * JSON串，描述舱位信息，key：航司+舱位代码，如“CA-Y”
	 **/
	public $cabin_info_map;
	
	/** 
	 * json格式字符串：描述出发/到达城市基本信息
	 **/
	public $city_info_map;
	
	/** 
	 * JSON串，表示航班具体信息,key为出发+达到+航班号，如："BJSSHAMU5162"。示例值：{“BJSSHAMU5102":{"depTime":1403178600000,"flightType":"333","depAirportCode":"PEK","fees":5000,"depTerm":"T2","flight":"MU5102","stops":0,"airlineCode":"MU","meals":1,"arrTerm":"T2","arrTime":1403178600000,"carrier":"","arrAirportCode":"SHA","taxes":14000}}
	 **/
	public $flight_info_map;
	
	/** 
	 * json格式字符串：描述当前搜索结果中航班机型信息， small表示小型机；middle表示中型机；large表示大型机
	 **/
	public $flight_type_info_map;
	
	/** 
	 * 保险分润扣钱规则说明（单位元）。{"insurePrice":20,//保险总价格
"insureAdultPrice":15,//保险成人分润价格
"insureChildPrice":0,//保险儿童分润价格
"insure9cPrice":20//春秋航空保险分润金额
}
	 **/
	public $insure_promotion_info_map_json;
	
	/** 
	 * 机票商品列表
	 **/
	public $items;	
}
?>