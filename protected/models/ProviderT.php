<?php
class ProviderT extends Provider {
    public static function getStationList() {
        return ProviderT::getProvider(self::PROVIDER_Q)->pGetStationList();
    }
    
    public static function getTrainList($departStationCode, $arriveStationCode, $departDate) {
        return ProviderT::getProvider(self::PROVIDER_Q)->pGetTrainList($departStationCode, $arriveStationCode, $departDate);
    }
    
    public static function getStopList($departStationCode, $arriveStationCode, $trainNo) {
        return ProviderT::getProvider(self::PROVIDER_Q)->pGetStopList($departStationCode, $arriveStationCode, $trainNo);
    }
}