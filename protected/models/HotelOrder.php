<?php
class HotelOrder extends QActiveRecord {
    
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return '{{hotelorder}}';
    }
    
    public function rules() {
        return array(
            array('hotelId, roomId, rateplanId, ctime, utime, status,roomCount,orderAmount', 'numerical', 'integerOnly' => True),
            array('bookName, guestName, oID', 'length', 'max' => 32),
            array('reason, specialRemark', 'length', 'max' => 64),
            array('checkIn, checkOut', 'length', 'max' => 10),
            array('bookPhone', 'length', 'max' =>11),
        );
    }
    
}