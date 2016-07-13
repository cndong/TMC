<?php
class CompanyFinanceLog extends QActiveRecord {
    const DEFAULT_NULL = 0;
    
    const TYPE_RECHARGE = 1;
    const TYPE_ORDER_PRICE = 2; //不含保险、邮费价格
    const TYPE_INSURE_PRICE = 3;
    const TYPE_INVOICE_PRICE = 4;
    const TYPE_RESIGN_PRICE = 5;
    const TYPE_REFUND = 6;
    public static $types = array(
        self::TYPE_RECHARGE => array('name' => '充值'),
        self::TYPE_ORDER_PRICE => array('name' => '票价(含税)', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{userName}>'),
        self::TYPE_INSURE_PRICE => array('name' => '保险', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{userName}>'),
        self::TYPE_INVOICE_PRICE => array('name' => '邮费', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{userName}>'),
        self::TYPE_RESIGN_PRICE => array('name' => '改签', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{userName}>，改签乘客:<{passengers}>'),
        self::TYPE_REFUND => array('name' => '退票', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{userName}>，退款乘客:<{passengers}>'),
    );
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{companyFinanceLog}}';
    }

    public function rules() {
        return array(
            array('userID, departmentID, companyID, businessID, type, orderID, income, payout, finance, info', 'required'),
            array('userID, departmentID, companyID, businessID, type, orderID, income, payout, finance, ctime, utime', 'numerical', 'integerOnly'=>true),
            array('info', 'length', 'max' => 500),
            array('id, userID, departmentID, companyID, businessID, type, orderID, income, payout, finance, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public static function create($companyID, $type, $order, $payout, $income, $info) {
        $company = $companyID;
        if (!($company instanceof Company) && !($company = Company::model()->findByPk($companyID))) {
            return F::errReturn(RC::RC_COM_NOT_EXISTS);
        }

        $attributes = array_fill_keys(array('userID', 'deparmentID', 'orderID', 'businessID'), self::DEFAULT_NULL);
        if (is_object($order)) {
            $reflects = array(
                'FlightCNOrder' => Dict::BUSINESS_FLIGHT,
                'TrainOrder' => Dict::BUSINESS_TRAIN,
                'BusOrder' => Dict::BUSINESS_BUS,
                'HotelOrder' => Dict::BUSINESS_HOTEL
            );
            $attributes['businessID'] = $reflects[get_class($order)];
            $attributes['orderID'] = $order->id;
            $attributes['userID'] = $order->userID;
            $attributes['departmentID'] = $order->departmentID;
        }
        
        $financeLog = new CompanyFinanceLog();
        $financeLog->companyID = $company->id;
        $financeLog->type = $type;
        $financeLog->payout = $payout;
        $financeLog->income = $income;
        $financeLog->finance = $company->finance;
        $financeLog->info = json_encode($info);
        $financeLog->attributes = $attributes;
        
        if (!$financeLog->save()) {
            Q::logModel($financeLog);
            return F::errReturn(RC::RC_MODEL_CREATE_ERROR);
        }
        
        return F::corReturn();
    }
    
    public function getInfoDes() {
        if (empty(self::$types[$this->type]['template']) || empty($this->info)) {
            return '';
        }
        
        $info = json_decode($this->info, True);
        
        return F::trQuoteTemplate(self::$types[$this->type]['template'], $info);
    }
    
    public static function search($params, $isGetCriteria = False) {
        $rtn = array('criteria' => Null, 'params' => array(), 'data' => array());
        
        $rtn['params'] = F::checkParams($params, array(
            'orderID' => '!' . ParamsFormat::INTNZ . '--0', 'userID' => '!' . ParamsFormat::INTNZ . '--0', 'departmentID' => '!' . ParamsFormat::INTNZ . '--0', 'companyID' => '!' . ParamsFormat::INTNZ . '--0',
            'businessID' => '!' . ParamsFormat::INTNZ . '--0', 'beginDate' => '!' . ParamsFormat::DATE . '--', 'endDate' => '!' . ParamsFormat::DATE . '--',
        ));
        
        $criteria = new CDbCriteria();
        $criteria->order = 'id DESC';
        foreach (array('orderID', 'userID', 'departmentID', 'companyID', 'businessID') as $type) {
            if (!empty($rtn['params'][$type])) {
                $criteria->compare($type, $params[$type]);
            }
        }
        
        if ($rtn['params']['beginDate']) {
            $criteria->compare('ctime', '>=' . strtotime($rtn['params']['beginDate']));
        }
        if ($rtn['params']['endDate']) {
            $criteria->compare('ctime', '<=' . strtotime($rtn['params']['endDate'] . ' 23:59:59'));
        }
        
        $rtn['criteria'] = $criteria;
        if ($isGetCriteria) {
            return $rtn;
        }
        
        $rtn['data'] = self::model()->findAll($criteria);
        
        return $rtn;
    }
}