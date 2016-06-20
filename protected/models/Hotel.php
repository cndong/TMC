<?php
class Hotel extends QActiveRecord {
    //public static $priceSelect = array(200=>array(0, 200), 400=>array(200, 400), 600=>array(400, 600), 800=>array(800, 80000));
    
    public static function isPriceSelect($select){
        return isset(self::$priceSelect[$select]);
    }
    
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
    
    public static function search($params, $isGetCriteria = False, $isWithRoute = True) {
        $return = array();    
        //城市代码, 星级(可选), 价格区间(可选), 经纬度(可选)
        $params = F::checkParams($params, array(
                'cityId' => ParamsFormat::HOTEL_ID ,
                'star' => '!' . ParamsFormat::INTNZ . '--0', 
                'lon' => '!' . ParamsFormat::FLOATNZ . '--0', 
                'lat' => '!' . ParamsFormat::FLOATNZ . '--0',
        ));
       if(!$params) return $return;
       
       $criteria = new CDbCriteria();
        foreach (array('cityId', 'star') as $type) {
            if (!empty($params[$type])) {
                $criteria->compare('t.' . $type, $params[$type]);
            }
        }
        if($params['lon'] && $params['lat'])
        $criteria->order = ' ACOS(SIN(('.$params['lat'].' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$params['lat'].' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$params['lon'].' * 3.1415) / 180 - (lon * 3.1415) / 180 ) ) * 6380 asc';
        $hotels = self::model()->findAll($criteria);
        foreach ($hotels as $hotel) {
             $hotel = $hotel->getAttributes(array('hotelId', 'hotelName', 'address', 'star', 'image', 'lowPrice'));
             $images = array('http://userimg.qunar.com/imgs/201501/21/66I5P26rcOOsfY2A6180.jpg', 'http://userimg.qunar.com/imgs/201501/21/66I5P26rcOOsfY2A6180.jpg');
             $hotel['image'] = $images[rand(0, 1)];
             $hotel['lowPrice'] = rand(100, 500);
             $return[] = $hotel;
        }
        return $return;
    }
    
    
}