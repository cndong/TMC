<?php
class Hotel extends QActiveRecord {
/*     public static $priceSelect = array(200=>array(0, 200), 400=>array(200, 400), 600=>array(400, 600), 800=>array(800, 80000));
    
    public static function isPriceSelect($select){
        return isset(self::$priceSelect[$select]);
    } */
    
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
            array('address, image', 'length', 'max' => 255),
            array('star, recommendedLevel', 'length', 'max' => 6),
            array('lon, lat', 'numerical'),
            array('intro, images, landmarks', 'length', 'max' => 1024),
        );
    }
    
}