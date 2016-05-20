<?php
class User extends QActiveRecord {
    const PASSWORD_SALT = 'zl52y@7*!*()@zysdfXSDy/';
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{user}}';
    }

    public function rules() {
        return array(
            array('mobile, name, companyID, departmentID, password', 'required'),
            array('companyID, departmentID, deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('mobile', 'length', 'max' => 11),
            array('name', 'length', 'max' => 50),
            array('password', 'length', 'max' => 32),
            array('id, mobile, name, companyID, departmentID, password, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function relations() {
        return array(
            'department' => array(self::BELONGS_TO, 'Department', 'departmentID'),
            'company' => array(self::BELONGS_TO, 'Company', 'companyID'),
            'addresses' => array(self::HAS_MANY, 'UserAddress', 'userID'),
            'contacters' => array(self::HAS_MANY, 'UserContacter', 'userID'),
            'passengers' => array(self::HAS_MANY, 'UserPassenger', 'userID')
        );
    }
    
    public function beforeSave() {
        if (parent::beforeSave()) {
            if ($this->isNewRecord) {
                $this->password = self::getHashPassword($this->password, $this->ctime);
            }
        }
        
        return True;
    }
    
    public function validatePassword($password) {
        return self::getHashPassword($password, $this->ctime) === $this->password;
    }
    
    public static function getHashPassword($password, $adminSalt) {
        return md5($password . $adminSalt . self::PASSWORD_SALT);
    }
    
    private static function _getCreateUserFormats() {
        return array(
            'mobile' => ParamsFormat::MOBILE,
            'name' => ParamsFormat::TEXTNZ,
            'companyID' => ParamsFormat::INTNZ,
            'departmentID' => ParamsFormat::INTNZ,
            'password' => ParamsFormat::TEXTNZ
        );
    }
    
    private static function _getCreateUserParams($params) {
        if (!($params = F::checkParams($params, self::_getCreateUserFormats()))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        if (!Department::model()->findByPk($params['departmentID'])) {
            return F::errReturn(RC::RC_DEP_NOT_EXISTS);
        }
        
        if (User::model()->findByAttributes(array('mobile' => $params['mobile']))) {
            return F::errReturn(RC::RC_USER_HAD_EXISTS);
        }
        
        return F::corReturn($params);
    }
    
    public static function createUser($params) {
        if (!F::isCorrect($res = self::_getCreateUserParams($params))) {
            return $res;
        }
        
        $user = new User();
        $user->attributes = $res['data'];
        if (!$user->save()) {
            Q::log('----------------');
            Q::log($user->getErrors(), 'dberror.createUser');
            Q::log($params, 'dberror.createUser');
            Q::log('----------------');
        
            return F::errReturn(RC::RC_USER_CREATE_ERROR);
        }
        
        return F::corReturn($user);
    }
    
    public static function search($params, $isGetCriteria = False) {
        $rtn = array('criteria' => Null, 'params' => array(), 'data' => array());
        $rtn['params'] = $params = F::checkParams($params, array(
            'companyID' => '!' . ParamsFormat::INTNZ . '--', 'departmentID' => '!' . ParamsFormat::INTNZ . '--', 'search' => '!' . ParamsFormat::TEXTNZ . '--'
        ));
        
        $criteria = new CDbCriteria();
        $criteria->with = array('department', 'company');
        $params['companyID'] && $criteria->compare('t.companyID', $params['companyID']);
        $params['departmentID'] && $criteria->compare('t.departmentID', $params['departmentID']);
        if ($params['search']) {
            $criteria->compare('t.id', $params['search']);
            $criteria->compare('t.mobile', $params['search']);
            $criteria->addSearchCondition('t.name', $params['search'], True, 'OR');
        }
        
        $rtn['criteria'] = $criteria;
        if ($isGetCriteria) {
            return $rtn;
        }
        
        $rtn['data'] = self::model()->findAll($criteria);
        
        return $rtn;
    }
    
    public function modifyPassword($password = '') {
        $this->password = User::getHashPassword($password, $this->ctime);
        if (!$this->save()) {
            return F::errReturn(RC::RC_USER_CHANGE_PASSWD_ERROR);
        }
        
        return F::corReturn();
    }
}