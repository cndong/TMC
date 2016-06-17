<?php
class Hotel extends QActiveRecord {
    
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return '{{hotel}}';
    }
    public function rules() {
        return array(
            array('hotelId, ctime, utime', 'numerical', 'integerOnly' => True),
            array('hotelName, telephone', 'length', 'max' => 32),
            array('countryId, provinceId, cityId', 'length', 'max' => 4),
            array('address', 'length', 'max' => 255),
            array('star', 'length', 'max' => 6),
            array('lon, lat', 'numerical'),
            array('intro, images, landmarks', 'length', 'max' => 1024),
        );
    }
}