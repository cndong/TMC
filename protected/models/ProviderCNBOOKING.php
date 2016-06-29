<?php
class ProviderCNBOOKING{
    /*
        MessageInfo/CODE
        30000 操作成功
        30001 输入 XML 为空
        30002 XML 格式不正确
        30003 XML 节点内容不合法
        30004 Action 不合法
        30005 接口授权已到期
        30006 接口未授权
        30007 安全码(SecurityKey)错误
        30008 用户名(UserName)错误
        30009 密码(PassWord)错误
        30010 传入签名 (Signature)错误
        
        Data/ReturnCode
        31001 预定校验不通过
        31002 预定校验通过 => PREBOOKINGCHECK_SUCCESS
        31003 客户信贷额度不足
        31004 预定成功
        31005 预定失败
        31007 修改成功
        31008 修改失败
        31009 订单取消成功
        31010 订单取消失败
        31011 订单已存在,请勿重复下单
        31012 酒店或房间已经下架
        31013 库存不足
     */
    
    const PREBOOKINGCHECK_SUCCESS= '31002';
    const BOOKING_SUCCESS= '31004';
    const BOOKING_CANCEL_SUCCESS= '31009';
    const BOOKING_SUCCESS_STATUS = 10;  // 订单状态大于等于10是已确认
    
    public static function request($method, $params=array(), $scrollingInfo = array('DisplayReq'=>40, 'PageNo'=>1)) {
        $return = array(
                'rc' => RC::RC_ERROR,
                'msg' => '',
                'data' => array()
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, QEnv::$providers[Dict::BUSINESS_HOTEL]['CNBOOKING']['WSDL_URL']);
        $postParams = array('xmlRequest'=>self::getRequestXML($method, $params, $scrollingInfo));
        Q::log($postParams, 'Provider.CNBOOKING.Request');
        if($postParams){
            curl_setopt($ch, CURLOPT_POST,count($postParams)) ;
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParams));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $ret = @curl_exec($ch);
        //错误自动重试一次
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
            $ret = @curl_exec($ch);
        }
        //Q::log($ret, 'Provider.CNBOOKING.Response');
        if($ret){
            $ret = (array)simplexml_load_string($ret);
            /*
             array(3) {
                      ["ActionName"]=>
                      string(11) "HotelSearch"
                      ["MessageInfo"]=>array(2) {
                                        ["Code"]=>
                                        string(5) "30000"
                                        ["Description"]=>
                                        string(12) "操作成功"
                                      }
                      ["Data"]=> array(1) {
                                            ["Hotels"]=>  array(2)
                                        }
                                '暂无数据'
                                
                    ["Data"] => array(2) {
                                 'ReturnCode' => '31001',
                                 'ReturnMessage' => '该字符串未被识别为有效的 DateTime。',
            */
            $ret['MessageInfo'] = (array) $ret['MessageInfo'];
            $return['data'] = is_object($ret['Data']) ? json_decode(json_encode($ret['Data']), true) : $ret['Data'];
            is_array($return['data']) && isset($return['data']['ReturnCode']) && Q::log($return, 'Provider.CNBOOKING.Response.Return');
            // Code 主要是查询返回的查询结果状态, ReturnCode主要是提交数据交互返回的业务处理状态
            if($ret['MessageInfo']['Code'] != '30000') {
                $return['rc'] = is_array($return['data']) && isset($return['data']['ReturnCode']) ? $return['data']['ReturnCode'] : $ret['MessageInfo']['Code'];
                $return['msg'] = is_array($return['data']) && isset($return['data']['ReturnCode']) ? $return['data']['ReturnMessage'] : $ret['MessageInfo']['Description'];
                Q::log($ret, 'Provider.CNBOOKING.Error');
            }else $return['rc'] = RC::RC_SUCCESS;
        }else Q::log('', 'Provider.CNBOOKING.Response.None');
        return $return;
    }
    
    public static function addXMLShell($actionName, $xml, $scrollingInfo) {
        $sequenceID = Q::getUniqueID();
        $dateTime = date('Y-m-d H:i:s', Q_TIME);
        $xml = str_replace(array("\n", "\r\r", "\t", '    '), '', $xml);
        return <<<EOF
<CNRequest>
     <ActionName>{$actionName}</ActionName>
     <IdentityInfo>
         <AppId>1</AppId>
         <SecurityKey>369b469c-51b2-43cd-9677-934ca17f2651</SecurityKey>
         <UserName>EN000001</UserName>
         <PassWord>E10ADC3949BA59ABBE56E057F20F883E</PassWord>
         <Signature>RU4wMDAwMDFFMTBBREMzOTQ5QkE1OUFCQkU1NkUwNTdGMjBGODgzRTM2OWI0NjljLTUxYjItNDNjZC05Njc3LTkzNGNhMTdmMjY1MQ==</Signature>
     </IdentityInfo>
     <ScrollingInfo>
        <DisplayReq>{$scrollingInfo['DisplayReq']}</DisplayReq>
        <PageItems>10</PageItems>
        <PageNo>{$scrollingInfo['PageNo']}</PageNo>
     </ScrollingInfo>
     <SearchConditions>{$xml}</SearchConditions>
</CNRequest>
EOF;
    }
    
    public static function getRequestXML($actionName, $params, $scrollingInfo) {
        $xml = call_user_func(array('ProviderCNBOOKING', "get{$actionName}XML"), $params);
        return self::addXMLShell($actionName, $xml, $scrollingInfo);
    }
    
    public static function getHotelSearchXML($params) {
        $hotelId = isset($params['HotelId']) ? $params['HotelId'] : '';
        return <<<EOF
<CountryId>{$params['CountryId']}</CountryId>
<ProvinceId>{$params['ProvinceId']}</ProvinceId>
<CityId>{$params['CityId']}</CityId>
<HotelId>{$hotelId}</HotelId>
<Lang>GB</Lang>
EOF;
return <<<EOF
<CountryId>0001</CountryId>
<ProvinceId>0100</ProvinceId>
<CityId>0101</CityId>
<HotelId></HotelId>
<Lang>GB</Lang>
EOF;
    }
    
    public static function getRoomSearchXML($params) {
        $roomId = isset($params['RoomId']) ? $params['RoomId'] : '';
        return <<<EOF
<CountryId>{$params['CountryId']}</CountryId>
<ProvinceId>{$params['ProvinceId']}</ProvinceId>
<CityId>{$params['CityId']}</CityId>
<HotelId>{$params['HotelId']}</HotelId>
<RoomId>{$roomId}</RoomId>
<Lang>GB</Lang>
EOF;
    }
    
    public static function getRatePlanSearchXML($params) {
        $cityId = isset($params['CityId']) ? $params['CityId'] : '';
        $hotelId = isset($params['HotelId']) ? $params['HotelId'] : '';
        $roomId = isset($params['RoomId']) ? $params['RoomId'] : '';
        $ratePlanId = isset($params['RatePlanId']) ? $params['RatePlanId'] : '';
        return <<<EOF
    <CountryId>{$params['CountryId']}</CountryId>
    <ProvinceId>{$params['ProvinceId']}</ProvinceId>
    <CityId>{$cityId}</CityId>
    <HotelId>{$hotelId}</HotelId>
    <RoomId>{$roomId}</RoomId>
    <RatePlanId>{$ratePlanId}</RatePlanId>
    <StayDateRange>
      <CheckIn>{$params['CheckIn']}</CheckIn>
      <CheckOut>{$params['CheckOut']}</CheckOut>
    </StayDateRange>
    <GuestInfo>
      <AdultCount></AdultCount>
      <ChildCount></ChildCount>
      <ChildAges></ChildAges>
    </GuestInfo>
    <Currency></Currency>
    <Lang>GB</Lang>
    <RatePlanOnly></RatePlanOnly>
EOF;
    }
    
    public static function getPreBookingCheckXML($params) {
        return <<<EOF
    <HotelId>{$params['HotelId']}</HotelId>
    <RoomId>{$params['RoomId']}</RoomId>
    <RateplanId>{$params['RateplanId']}</RateplanId>
    <CheckIn>{$params['CheckIn']}</CheckIn>
    <CheckOut>{$params['CheckOut']}</CheckOut>
    <RoomCount>{$params['RoomCount']}</RoomCount>
    <Currency>CNY</Currency>
    <OrderAmount>{$params['OrderAmount']}</OrderAmount>
EOF;
    }
    
    public static function getBookingXML($params) {
        $specialRemark = isset($params['SpecialRemark']) ? $params['SpecialRemark'] : '';
        return <<<EOF
        <HotelId>{$params['HotelId']}</HotelId>
        <RoomId>{$params['RoomId']}</RoomId>
        <RateplanId>{$params['RateplanId']}</RateplanId>
            <CheckIn>{$params['CheckIn']}</CheckIn>
            <CheckOut>{$params['CheckOut']}</CheckOut>
            <RoomCount>{$params['RoomCount']}</RoomCount>
            <Currency>CNY</Currency>
            <OrderAmount>{$params['OrderAmount']}</OrderAmount>
    <BookInfo>
      <BookName>{$params['BookName']}</BookName>
      <BookPhone>{$params['BookPhone']}</BookPhone>
    </BookInfo>
    <GuestInfo>
      <GuestName>{$params['GuestName']}</GuestName>
      <GuestPhone>
      </GuestPhone>
      <GuestFax>
      </GuestFax>
      <GuestType>
      </GuestType>
      <CardTypeId>
      </CardTypeId>
      <CardNum>
      </CardNum>
    </GuestInfo>
    <SpecialRemark>{$specialRemark}</SpecialRemark>
    <Reserve>
      <Reserve1>
      </Reserve1>
      <Reserve2>
      </Reserve2>
    </Reserve>
    <CustomerOrderId>{$params['CustomerOrderId']}</CustomerOrderId>
EOF;
    }
    
    public static function getBookingCancelXML($params) {
        return <<<EOF
        <OrderId>{$params['OrderId']}</OrderId>
EOF;
    }
    
/*     public static function getOrderSearchXML($params) {
    } */
}