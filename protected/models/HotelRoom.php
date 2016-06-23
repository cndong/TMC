<?php
class HotelRoom extends QActiveRecord {
    
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return '{{hotelroom}}';
    }
    
    public function rules() {
        return array(
            array('hotelId, ctime, utime', 'numerical', 'integerOnly' => True),
            array('hotelName, telephone', 'length', 'max' => 32),
            array('countryId, provinceId, cityId', 'length', 'max' => 4),
            array('address, image', 'length', 'max' => 255),
            array('star, recommendedLevel', 'length', 'max' => 6),
            array('lon, lat', 'numerical'),
            array('intro, images, landmarks', 'length', 'max' => 1024),
        );
    }
    
}