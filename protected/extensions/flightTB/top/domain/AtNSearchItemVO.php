<?php

/**
 * 国内机票搜索商品定义
 * @author auto create
 */
class AtNSearchItemVO
{
	
	/** 
	 * 活动优惠金额
	 **/
	public $activity_bonus;
	
	/** 
	 * 代理人编号
	 **/
	public $agent_id;
	
	/** 
	 * JSON串，保存商品扩展信息，相关Key:site(描述是否是极速出票)，I_A_K_P_T_L_U（PC淘客搜索URL），I_A_K_H_T_L_U（H5淘客搜索URL），I_A_K_P_T_D_U（PC淘客下单页URL），I_A_K_H_T_D_U（H5淘客下单页URL）,I_A_K_T_G_Q_01（去程退改签），I_A_K_T_G_Q_10（回程退改签）
	 **/
	public $attributes;
	
	/** 
	 * 基准舱位价格，单位分，往返是来回程各段的基准价格之和。
	 **/
	public $basic_cabin_price;
	
	/** 
	 * 是否强制保险
	 **/
	public $force_insure;
	
	/** 
	 * 强制保险金额，单位分。(值为每一Segment强制保险金额的总和)
	 **/
	public $force_insure_price;
	
	/** 
	 * 获取保险分润后的价格，不包含活动。不支持分润时返回0
	 **/
	public $insure_price;
	
	/** 
	 * 是否是旗舰店商品。
	 **/
	public $is_qijian;
	
	/** 
	 * 商品结果类型，0普通单程，1组合往返；2打包往返；3往返直减
	 **/
	public $item_type;
	
	/** 
	 * 销售价格，单位分
	 **/
	public $price;
	
	/** 
	 * 商品航段数据信息
	 **/
	public $segments;
	
	/** 
	 * 是否支持保险分润
	 **/
	public $support_insure_promotion;
	
	/** 
	 * 产品票面价，单位分。往返是来回程各段票面价之和
	 **/
	public $ticket_price;
	
	/** 
	 * 航程类型，0单程，1往返
	 **/
	public $trip_type;	
}
?>