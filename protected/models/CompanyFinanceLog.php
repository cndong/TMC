<?php
class CompanyFinanceLog extends QActiveRecord {
    const TYPE_RECHARGE = 1;
    const TYPE_ORDER_PRICE = 2; //不含保险、邮费价格
    const TYPE_INSURE_PRICE = 3;
    const TYPE_INVOICE_PRICE = 4;
    const TYPE_RESIGN_PRICE = 5;
    const TYPE_REFUND = 6;
    public static $types = array(
        self::TYPE_RECHARGE => array('name' => '充值'),
        self::TYPE_ORDER_PRICE => array('name' => '票价(含税)', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{$userName}>'),
        self::TYPE_INSURE_PRICE => array('name' => '保险', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{$userName}>'),
        self::TYPE_INVOICE_PRICE => array('name' => '邮费', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{$userName}>'),
        self::TYPE_RESIGN_PRICE => array('name' => '改签', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{$userName}>'),
        self::TYPE_REFUND => array('name' => '退票', 'template' => '订单号:<{orderID}>，部门:<{departmentName}>，用户:<{$userName}>')
    );
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{companyFinanceLog}}';
    }

    public function rules() {
        return array(
            array('companyID, type, income, payout, finance, info', 'required'),
            array('companyID, type, income, payout, finance, ctime, utime', 'numerical', 'integerOnly'=>true),
            array('info', 'length', 'max' => 255),
            array('id, companyID, type, income, payout, finance, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public static function create($companyID, $type, $payout, $income, $info) {
        $company = $companyID;
        if (!($company instanceof Company) && !($company = Company::model()->findByPk($companyID))) {
            return F::errReturn(RC::RC_COM_NOT_EXISTS);
        }
        
        $financeLog = new CompanyFinanceLog();
        $financeLog->companyID = $company->id;
        $financeLog->type = $type;
        $financeLog->payout = $payout;
        $financeLog->income = $income;
        $financeLog->finance = $company->finance;
        $financeLog->info = json_encode($info);
        
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
}