<?php
class ScriptController extends BossController {
    
    public function actionGetHotel($hotelId, $cityId) {
        Hotel::getHotelFromCity($hotelId, $cityId);
    }
    
    public function actionUpdateHotel($hoteId) {
        Hotel::updateHotel($hoteId);
    }
}