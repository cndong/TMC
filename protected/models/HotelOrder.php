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
            array('merchantID, userID, departmentID, companyID, hotelId, roomId, rateplanId, ctime, utime, status,roomCount, orderPrice, bedLimit, breakfast, reviewerID', 'numerical'),
            array('hotelName, bookName, guestName, oID, roomName', 'length', 'max' => 32),
            array('reason, specialRemark', 'length', 'max' => 64),
            array('checkIn, checkOut', 'length', 'max' => 10),
            array('bookPhone', 'length', 'max' =>11),
            array('lastCancelTime', 'type', 'type'=>'datetime', 'datetimeFormat'=>'yyyy-MM-dd hh:mm:ss',),
        );
    }
    
    public function relations() {
        return array(
                'user' => array(self::BELONGS_TO, 'User', 'userID'),
                'department' => array(self::BELONGS_TO, 'Department', 'departmentID'),
                'company' => array(self::BELONGS_TO, 'Company', 'companyID'),
                'reviewer' => array(self::BELONGS_TO, 'User', 'reviewerID'),
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
    
    private function _cS2CheckSuccAfter() {
        if(F::isCorrect($res= ProviderCNBOOKING::request('Booking', array(
                'HotelId' => $this->hotelId,
                'RoomId' => $this->roomId,
                'RateplanId' => $this->rateplanId,
                'CheckIn' => $this->checkIn,
                'CheckOut' => $this->checkOut,
                'RoomCount' => $this->roomCount,
                'OrderAmount' => $this->orderPrice,
                'BookName' => $this->bookName,
                'BookPhone' => $this->bookPhone,
                'GuestName' => $this->guestName,
                'SpecialRemark' =>'',
                'CustomerOrderId' => $this->id,
        ))) && $res['data']){
            if(is_array($res['data']) && $res['data']['ReturnCode'] == ProviderCNBOOKING::BOOKING_SUCCESS){
                if($res['data']['Order']['OrderStatusId']>=ProviderCNBOOKING::BOOKING_SUCCESS_STATUS){
                    if($return = $this->_changeFinance()){
                        if(!$this->updateByPk($this->getPrimaryKey(), array('status'=>HotelStatus::BOOK_SUCC, 'oID'=>$res['data']['Order']['OrderId']))){
                            $this->_mailAlert('status');
                            $return = F::errReturn(RC::RC_DB_ERROR);
                        }
                    }
                }else{
                    $this->_mailAlert('booking');
                    $return = F::errReturn(RC::RC_H_HOTEL_BOOKING_ERROR);
                }
            }else{
                $this->_mailAlert($res['data']['ReturnMessage']);
                $return = F::errReturn($res['data']['ReturnCode'], $res['data']['ReturnMessage']);
            }
        }
        return $return;
    }
    
    private function _changeFinance() {
        $userTAOPrice = $this->orderPrice*100;
        if (!$this->isPrivate) {
            $info = array('orderID' => $this->id, 'departmentName' => $this->department->name, 'userName' => $this->user->name);
            $company = Company::model()->findByPk($this->companyID);
            if (!F::isCorrect($res = $company->changeFinance(CompanyFinanceLog::TYPE_ORDER_PRICE, $this, $userTAOPrice, 0, $info))) {
                return $res;
            }
        }
        return F::corReturn();
    }
    
    //成功发送 短信和app推送
    private function _nofity() {
    
    }
    
    //邮件报警
    private function _mailAlert($msg) {
        $cpl['tplInfo']['orderID'] = $this->id;
        $cpl['tplInfo']['msg'] = $msg;
        @Mail::sendMail($cpl, 'Hotel.SyncFailed');
        Q::log($msg, 'dberror.hotel.syncFailed');
    }
    
    public static function search($params, $isGetCriteria = False, $isWithRoute = True) {
        $rtn = array('criteria' => Null, 'params' => array(), 'data' => array());
    
        $defaultBeginDate = date('Y-m-d', strtotime('-1 week'));
        $rtn['params'] = F::checkParams($params, array(
                'orderID' => '!' . ParamsFormat::INTNZ . '--0', 'userID' => '!' . ParamsFormat::INTNZ . '--0', 'departmentID' => '!' . ParamsFormat::INTNZ . '--0', 'companyID' => '!' . ParamsFormat::INTNZ . '--0', 'operaterID' => '!' . ParamsFormat::INTNZ . '--0',
                'beginDate' => '!' . ParamsFormat::DATE . '--' . $defaultBeginDate, 'endDate' => '!' . ParamsFormat::DATE . '--' . Q_DATE,
                'status' => '!' . ParamsFormat::INTNZ . '--0', 
        ));
    
        $criteria = new CDbCriteria();
        $criteria->with = array_keys(self::model()->relations());
        $criteria->order = 't.id DESC';
        foreach (array('orderID', 'userID', 'departmentID', 'companyID', 'operaterID', 'status') as $type) {
            if (!empty($rtn['params'][$type])) {
                $field = $type == 'orderID' ? 'id' : $type;
                $criteria->compare('t.' . $field, $params[$type]);
            }
        }
        $criteria->addBetweenCondition('t.ctime', strtotime($rtn['params']['beginDate']), strtotime($rtn['params']['endDate'] . ' 23:59:59'));
        
        $rtn['criteria'] = $criteria;
        if ($isGetCriteria) {
            return $rtn;
        }
    
        $orders = F::arrayAddField(self::model()->findAll($criteria), 'id');
        $rtn['data'] = $orders;
        return $rtn;
    }
}