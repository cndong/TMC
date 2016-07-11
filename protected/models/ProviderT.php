<?php
class ProviderT extends Provider {
    public static function getStationList() {
        return ProviderT::getProvider(self::PROVIDER_Q)->pGetStationList();
    }
    
    public static function getTrainList($params, $isReload = False) {
        if (!F::checkParams($params, array('departStationCode' => ParamsFormat::T_STATION_CODE, 'arriveStationCode' => ParamsFormat::T_STATION_CODE, 'departDate' => ParamsFormat::DATE))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        return F::corReturn(ProviderT::getProvider(self::PROVIDER_Q)->pGetTrainList($params, $isReload));
    }
    
    public static function getStopList($params) {
        if (!F::checkParams($params, array('departStationCode' => ParamsFormat::T_STATION_CODE, 'arriveStationCode' => ParamsFormat::T_STATION_CODE, 'trainNo' => ParamsFormat::T_TRAIN_NO))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        return F::corReturn(ProviderT::getProvider(self::PROVIDER_Q)->pGetStopList($params));
    }
    
    public static function book($order) {
        return self::getProvider(self::PROVIDER_Q)->pBook($order);
    }
    
    public static function refund($order, $ticket) {
        return self::getProvider(self::PROVIDER_Q)->pRefund($order, $ticket);
    }
}