<?php
class HotelImage extends QActiveRecord {
        
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return '{{hotel_image}}';
    }
    
    public function rules() {
        return array(
            array('ImageId,hotelId, ctime, utime', 'numerical', 'integerOnly' => True),
            array('ImageName', 'length', 'max' => 64),
            array('ImageUrl', 'length', 'max' => 255),
        );
    }
    
}