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
            array('merchantID, userID, hotelId, roomId, rateplanId, ctime, utime, status,roomCount,orderAmount', 'numerical', 'integerOnly' => True),
            array('hotelName, bookName, guestName, oID', 'length', 'max' => 32),
            array('reason, specialRemark', 'length', 'max' => 64),
            array('checkIn, checkOut', 'length', 'max' => 10),
            array('bookPhone', 'length', 'max' =>11),
            array('lastCancelTime', 'type', 'type'=>'datetime', 'datetimeFormat'=>'yyyy-MM-dd hh:mm:ss',),
        );
    }
    
    
    public static function createOrder($params) {
/*         if (!F::isCorrect($res = self::_checkCreateOrderParams($params))) {
            return $res;
        } */
        $return = F::errReturn(RC::RC_ERROR);
        $train = Yii::app()->db->beginTransaction();
        try {
            $order = new HotelOrder();
            $order->attributes = $params;
            isset($params['lastCancelTime']) && $params['lastCancelTime'] && $order->lastCancelTime = date('Y-m-d H:i:s', strtotime($params['lastCancelTime']));
            $order->save();
            if(F::isCorrect($res= ProviderCNBOOKING::request('Booking',
                    array(
                            'HotelId' => $_POST['hotelId'],
                            'RoomId' => $_POST['roomId'],
                            'RateplanId' => $_POST['rateplanId'],
                            'CheckIn' => $_POST['checkIn'],
                            'CheckOut' => $_POST['checkOut'],
                            'RoomCount' => $_POST['roomCount'],
                            'OrderAmount' => $_POST['orderPrice'],
                            'BookName' => $_POST['bookName'],
                            'BookPhone' => $_POST['bookPhone'],
                            'GuestName' => $_POST['guestName'],
                            'SpecialRemark' => isset($params['specialRemark']) ? $params['specialRemark'] : '',
                            'CustomerOrderId' => $order->id,
                    ))) && $res['data']){
                if(is_array($res['data']) && $res['data']['ReturnCode'] == ProviderCNBOOKING::BOOKING_SUCCESS){
                    if($res['data']['Order']['OrderStatusId']>=ProviderCNBOOKING::BOOKING_SUCCESS_STATUS){
                        $return = F::corReturn(array('orderId'=>$order->id));
                        if(!$order->updateByPk($order->getPrimaryKey(), array('status'=>HotelStatus::WAIT_CHECK, 'oID'=>$res['data']['Order']['OrderId']))){//状态未更新则邮件报警
                            $cpl['tplInfo']['orderID'] = $order->id ;
                            @Mail::sendMail($cpl, 'Hotel.SyncFailed');
                            Q::log($e->getMessage(), 'dberror.hotel.syncFailed');
                        }
                    }else $return = F::errReturn(RC::RC_H_HOTEL_BOOKING_ERROR);
                }else $return = F::errReturn($res['data']['ReturnCode'], $res['data']['ReturnMessage']);
            }
    
            if(F::isCorrect($return)){
                $train->commit();
                Log::add(Log::TYPE_HOTEL, $order->id, array('status' => $order->status, 'isSucc' => True));
            }else $train->rollback();
            return $return;
        } catch (Exception $e) {
            $train->rollback();
            Q::log($e->getMessage(), 'dberror.hotel.createOrder');
            return F::errReturn(RC::RC_DB_ERROR, $e->getMessage());
        }
    }
}