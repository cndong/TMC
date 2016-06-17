<?php
class ProviderCNBOOKING{
    //$ret['Data'] => array|'暂无数据'
    public static function request($method, $params=array()) {
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
        Q::log($ret, 'Provider.CNBOOKING.Response');
        if($ret){
            $ret = (array)simplexml_load_string($ret);
            $ret['MessageInfo'] = (array) $ret['MessageInfo'];
            $ret['Data'] = is_object($ret['Data']) ? (array) $ret['Data'] : $ret['Data'];
            if(!$ret['MessageInfo']['Code'] = '30000') Q::log($ret, 'Provider.CNBOOKING.Error');
            return $ret;
        }else return false;
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
        return <<<EOF
<CountryId>{$params['CountryId']}</CountryId>
<ProvinceId>{$params['ProvinceId']}</ProvinceId>
<CityId>{$params['CityId']}</CityId>
<HotelId></HotelId>
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
        return <<<EOF
<CountryId>{$params['CountryId']}</CountryId>
<ProvinceId>{$params['ProvinceId']}</ProvinceId>
<CityId>{$params['CityId']}</CityId>
<HotelId>{$params['HotelId']}</HotelId>
<RoomId></RoomId>
<Lang>GB</Lang>
EOF;
    }
    
}