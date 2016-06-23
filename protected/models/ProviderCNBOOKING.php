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
    
    public static function request($method, $params=array()) {
        $return = array(
                'rc' => RC::RC_ERROR,
                'msg' => '',
                'data' => array()
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, QEnv::$providers[Dict::BUSINESS_HOTEL]['CNBOOKING']['WSDL_URL']);
        $postParams = array('xmlRequest'=>self::getRequestXML($method, $params));
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
                                ["Hotels"]=>
                                array(2) {
                                
                    ["Data"] => array(2) {
                                 'ReturnCode' => '31001',
                                 'ReturnMessage' => '该字符串未被识别为有效的 DateTime。',
                      $ret['Data'] => array|'暂无数据'          
            */
            $ret['MessageInfo'] = (array) $ret['MessageInfo'];
            isset($ret['Data']['ReturnCode']) && Q::log($ret, 'Provider.CNBOOKING.Response.Return');
            if($ret['MessageInfo']['Code'] != '30000') {
                $ret['Data'] = (array) $ret['Data'];
                $return['rc'] = isset($ret['Data']['ReturnCode']) ? $ret['Data']['ReturnCode'] : $ret['MessageInfo']['Code'];
                $return['msg'] = isset($ret['Data']['ReturnMessage']) ? $ret['Data']['ReturnMessage'] : $ret['MessageInfo']['Description'];
                Q::log($ret, 'Provider.CNBOOKING.Error');
            }else {
                $return['rc'] = RC::RC_SUCCESS;
                $return['data'] = is_object($ret['Data']) ? json_decode(json_encode($ret['Data']), true) : $ret['Data'];
                if(!is_object($ret['Data'])) Q::log($ret, 'Provider.CNBOOKING.Response.Data.None');
            }
        }
        return $return;
    }
    
    public static function addXMLShell($actionName, $xml) {
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
        <DisplayReq>40</DisplayReq>
        <PageItems>10</PageItems>
        <PageNo>1</PageNo>
     </ScrollingInfo>
        <SearchConditions>{$xml}</SearchConditions>
</CNRequest>
EOF;
    }
    
    public static function getRequestXML($actionName, $params) {
        $xml = call_user_func(array('ProviderCNBOOKING', "get{$actionName}XML"), $params);
        return self::addXMLShell($actionName, $xml);
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
        $roomId = isset($params['RoomId']) ? $params['RoomId'] : '';
        $ratePlanId = isset($params['RatePlanId']) ? $params['RatePlanId'] : '';
        return <<<EOF
    <CountryId>{$params['CountryId']}</CountryId>
    <ProvinceId>{$params['ProvinceId']}</ProvinceId>
    <CityId>{$params['CityId']}</CityId>
    <HotelId>{$params['HotelId']}</HotelId>
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
}