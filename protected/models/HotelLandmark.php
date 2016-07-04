<?php
class HotelLandmark extends QActiveRecord {
        
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return '{{hotel_landmark}}';
    }
    
    public function rules() {
        return array(
            array('Landid,hotelId, ctime, utime', 'numerical', 'integerOnly' => True),
            array('LandName', 'length', 'max' => 64),
        );
    }
    
}