<?php
class SMSCode extends QActiveRecord {
    const TYPE_FORGET_PASSWD = 1;
    
    const STATUS_SENDED = 1;
    const STATUS_VERIFY_FAILED = 2;
    const STATUS_VERIFY_SUCCEED = 3;
    
    public static $codeTypes = array(
        self::TYPE_FORGET_PASSWD => array('name' => '忘记密码', 'duration' => 1800, 'length' => 6, 'ctype' => 'num'),
    );
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{smsCode}}';
    }

    public function rules() {
        return array(
            array('mobile, code, type, status', 'required'),
            array('type, status, ctime, utime', 'numerical', 'integerOnly' => true),
            array('mobile', 'length', 'max' => 11),
            array('code', 'length', 'max' => 6),
            array('id, mobile, code, type, status, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public static function getCode($type) {
        return F::getHash(self::$codeTypes[$type]['length'], self::$codeTypes[$type]['ctype']);
    }
    
    public static function getByType($mobile, $type, $status) {
        $criteria = new CDbCriteria();
        $criteria->compare('mobile', $mobile);
        $criteria->compare('type', $type);
        $criteria->compare('status', $status);
        $criteria->addCondition('ctime>:ctime');
        $criteria->params[':ctime'] = Q_TIME - self::$codeTypes[$type]['duration'];
        
        return self::model()->find($criteria);
    }
    
    public static function send($params, $type) {
        if (!F::checkParams($params, array('mobile' => ParamsFormat::MOBILE))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $tran = Yii::app()->db->beginTransaction();
        try {
            $smsCode = new SMSCode();
            $smsCode->attributes = array(
                'mobile' => $params['mobile'],
                'code' => SMSCode::getCode(SMSCode::TYPE_FORGET_PASSWD),
                'type' => $type,
                'status' => SMSCode::STATUS_SENDED
            );
            if (!$smsCode->save()) {
                Q::log('---------创建code失败起始----------');
                Q::log($smsCode->getErrors());
                Q::log('---------创建code失败起始----------');
                throw new Exception(RC::RC_MODEL_CREATE_ERROR);
            }
            
            if (!F::isCorrect($res = SMS::send(array('mobile' => $params['mobile'], 'code' => $smsCode->code), SMSTemplate::FORGET_PASSWD))) {
                throw new Exception($res['rc']);
            }
            
            $tran->commit();
        } catch (Exception $e) {
            $tran->rollback();
            return F::errReturn($e->getMessage());
        }
        
        return F::corReturn($smsCode);
    }
    
    public function verify($code) {
        if ($this->status != self::STATUS_SENDED) {
            return F::errReturn(RC::RC_SMS_CODE_HAD_SENDED);
        }
        
        $this->status = $code == $this->code ? self::STATUS_VERIFY_SUCCEED : self::STATUS_VERIFY_FAILED;
        if (!$this->save()) {
            Q::logModel($this);
            return F::errReturn(RC::RC_MODEL_UPDATE_ERROR);
        }
        
        return $this->status == self::STATUS_VERIFY_SUCCEED ? F::corReturn() : F::errReturn(RC::RC_SMS_CODE_NOT_CORRECT);
    }
}