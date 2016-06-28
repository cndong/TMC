<?php
class HotelOrder extends QActiveRecord {
    private $_collectParams = array();
    
    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName() {
        return '{{hotelorder}}';
    }
    
    public function rules() {
        return array(
            array('merchantID, userID, departmentID, companyID, hotelId, roomId, rateplanId, ctime, utime, status,roomCount, orderPrice, bedLimit, breakfast', 'numerical'),
            array('hotelName, bookName, guestName, oID, roomName', 'length', 'max' => 32),
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
        if (!($user = User::model()->findByPk($_POST['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            return F::errReturn(RC::RC_USER_NOT_EXISTS);
        }else{
            $params['companyID'] = $user->companyID;
            $params['departmentID'] = $user->departmentID;
        }
        
        $return = F::errReturn(RC::RC_ERROR);
        $train = Yii::app()->db->beginTransaction();
        try {
            $order = new HotelOrder();
            $order->attributes = $params;
            isset($params['lastCancelTime']) && $params['lastCancelTime'] && $order->lastCancelTime = date('Y-m-d H:i:s', strtotime($params['lastCancelTime']));
            $order->save();
            $return = F::corReturn(array('orderId'=>$order->id));
            $train->commit();
            return $return;
        } catch (Exception $e) {
            $train->rollback();
            Q::log($e->getMessage(), 'dberror.hotel.createOrder');
            return F::errReturn(RC::RC_DB_ERROR);
        }
    }
    
    private function _setCollectParams($status, $params, $isMerge = True) {
        $this->_collectParams[$status] = $isMerge ? CMap::mergeArray($this->_collectParams, $params) : $params;
    }
    
    private function _getCollectParams($status) {
        return isset($this->_collectParams[$status]) ? $this->_collectParams[$status] : array();
    }
    
    public function changeStatus($status, $params = array(), $condition = '', $conditionParams = array()) {
        if (!FlightStatus::isFlightStatus($status)) {
            return F::errReturn(RC::RC_STATUS_NOT_EXISTS);
        }
    
        $isUserOp = $isSysHd = $isSysOp = False;
        if (!HotelStatus::isJumpCheck($status)) {
            $isUserOp = HotelStatus::isUserOp($this->status, $status);
            $isSysHd = HotelStatus::isSysHd($this->status, $status);
            $isSysOp = HotelStatus::isSysOp($this->status, $status);
            if (!($isUserOp || $isSysHd || $isSysOp)) {
                return F::errReturn(RC::RC_STATUS_NOT_OP);
            }
    
            if (($isSysHd || $isSysOp) && empty($params['operaterID'])) {
                return F::errReturn(RC::RC_STATUS_NO_OPERATER);
            }
    
            if ($func = HotelStatus::getCheckFunc($status)) {
                if (!$this->$func()) {
                    return F::errReturn(RC::RC_STATUS_NOT_MATCH_CHECK);
                }
            }
        }
    
        $toStatusConfig = HotelStatus::$hotelStatus[$status];
        $trans = Yii::app()->db->beginTransaction();
        try {
            $res = F::$return;
            $beforeMethodName = '_cS2' . $toStatusConfig['str'] . 'Before';
            $methodName = '_cS2' . $toStatusConfig['str'];
            $afterMethodName = '_cS2' . $toStatusConfig['str'] . 'After';
    
            $params['status'] = $status;
            $tmp = $isSysHd || $isSysOp ? array('status' => $status, 'operaterID' => $params['operaterID']) : array('status' => $status);
            $tmp = array('params' => $tmp, 'condition' => '', 'conditionParams' => array());
            if (method_exists($this, $beforeMethodName)) {
                if (F::isCorrect($res = $this->$beforeMethodName($params, $condition, $conditionParams))) {
                    if (!empty($res['data']) && is_array($res['data'])) {
                        $tmp = CMap::mergeArray($tmp, $res['data']);
                    }
                }
            }
            if (F::isCorrect($res)) {
                $func = method_exists($this, $methodName) ? $methodName : '_changeStatus';
                if (F::isCorrect($res = $this->$func($tmp['params'], $condition, $conditionParams)) && method_exists($this, $afterMethodName)) {
                    $res = $this->$afterMethodName();
                }
            }
    
            $func = F::isCorrect($res) ? 'commit' : 'rollback';
            $trans->$func();
        } catch (Exception $e) {
            Q::log($e->getMessage(), 'dberror.changeStatus');
    
            $trans->rollback();
            $res = F::errReturn(RC::RC_DB_ERROR);
        }
    
        $this->_setCollectParams($status, array(), False);
        Log::add(Log::TYPE_HOTEL, $this->id, array('status' => $this->status, 'isSucc' => F::isCorrect($res), 'params' => $params, 'res' => $res));
        return $res;
    }
    
    private function _changeStatus($sets, $condition = '', $conditionParams = array()) {
        $newSets = $sets;
        $sets = array();
        foreach ($newSets as $k => $v) {
            if ($this->hasAttribute($k)) {
                $sets[$k] = $v;
            }
        }
    
        $sets['utime'] = Q_TIME;
        if (FlightStatus::isAdminOp($this->status, $sets['status'])) {
            $condition .= empty($condition) ? '' : ' AND';
            $condition .= ' operaterID=:operaterID';
            $conditionParams[':operaterID'] = $sets['operaterID'];
        }
    
        $condition .= empty($condition) ? '' : ' AND';
        $condition .= ' status=:status';
        $conditionParams[':status'] = $this->status;
        if (!self::model()->updateByPk($this->id, $sets, $condition, $conditionParams)) {
            return F::errReturn(RC::RC_STATUS_CHANGE_ERROR);
        }
    
        $this->setAttributes($sets);
    
        return F::corReturn();
    }
    
    private function _cS2ApplyRfdBefore($params) {
        return F::corReturn(array('params'=>array('status'=>HotelStatus::RFDED)));
        if(F::isCorrect($res= ProviderCNBOOKING::request('BookingCancel',
                array(
                        'OrderId' => $params['oID'],
                ))) && $res['data']){
            if(is_array($res['data']) && $res['data']['ReturnCode'] == ProviderCNBOOKING::BOOKING_CANCEL_SUCCESS){
                return F::corReturn(array('params'=>array('status'=>HotelStatus::RFDED)));  //申请退房成功就是已退款了
            }else return F::errReturn($res['data']['ReturnCode'], $res['data']['ReturnMessage']); //return F::errReturn(RC::RC_H_HOTEL_BOOKING_CANCEL_ERROR);
        }
    }
    
    private function _checkBefore($params) {
        if (empty($params['reviewerID'])) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
    
        $rtn = array();
        if (!($params['reviewerID'] instanceof User)) {
            if (!($params['reviewerID'] = User::model()->findByPk($params['reviewerID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
                return F::errReturn(RC::RC_USER_NOT_EXISTS);
            }
        }
    
        if (!$params['reviewerID']->isReviewer) {
            return F::errReturn(RC::RC_HAVE_NO_REVIEW_PRIVILEGE);
        }
    
        return F::corReturn(array('params' => array('reviewerID' => $params['reviewerID']->id)));
    }
    
    private function _cS2CheckFailBefore($params) {
        return $this->_checkBefore($params);
    }
    
    private function _cS2CheckSuccBefore($params) {
        return $this->_checkBefore($params);
    }
    
/*     private function _cS2CheckSuccAfter() {
        /*   if(F::isCorrect($res= ProviderCNBOOKING::request('Booking',
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
        }else $train->rollback(); */
        return F::corReturn(array('params' => array('reviewerID' => $params['reviewerID']->id)));
    } */
}