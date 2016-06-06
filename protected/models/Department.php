<?php
class Department extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return '{{department}}';
    }

    public function rules() {
        return array(
            array('name, companyID', 'required'),
            array('companyID, deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('name', 'length', 'max' => 50),
            array('id, name, companyID, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function relations() {
        return array(
            'company' => array(self::BELONGS_TO, 'Company', 'companyID'),
            'users' => array(self::HAS_MANY, 'User', 'departmentID'),
        );
    }
    
    private static function _getCreateDepartmentFormats() {
        return array(
            'companyID' => ParamsFormat::INTNZ,
            'name' => ParamsFormat::TEXTNZ
        );
    }
    
    private static function _getCreateDepartmentParams($params) {
        if (!($params = F::checkParams($params, self::_getCreateDepartmentFormats()))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        if (!Company::model()->findByPk($params['companyID'])) {
            return F::errReturn(RC::RC_COM_NOT_EXISTS);
        }
        
        if (Department::model()->findByAttributes(array('companyID' => $params['companyID'], 'name' => $params['name']))) {
            return F::errReturn(RC::RC_DEP_HAD_EXISTS);
        }
        
        return F::corReturn($params);
    }
    
    public static function createDepartment($params) {
        if (!F::isCorrect($res = self::_getCreateDepartmentParams($params))) {
            return $res;
        }
        
        $department = new Department();
        $department->attributes = $res['data'];
        if (!$department->save()) {
            Q::logModel($department);
            return F::errReturn(RC::RC_DEP_CREATE_ERROR);
        }
        
        return F::corReturn($department);
    }
    
    public static function search($params, $isGetCriteria = False) {
        $rtn = array('criteria' => Null, 'params' => array(), 'data' => array());
        $rtn['params'] = $params = F::checkParams($params, array(
            'companyID' => '!' . ParamsFormat::INTNZ . '--', 'search' => '!' . ParamsFormat::TEXTNZ . '--'
        ));
        
        $criteria = new CDbCriteria();
        $criteria->with = 'company';
        $params['companyID'] && $criteria->compare('companyID', $params['companyID']);
        if ($params['search']) {
            $criteria->compare('t.id', $params['search']);
            $criteria->addSearchCondition('t.name', $params['search'], True, 'OR');
        }
        
        $rtn['criteria'] = $criteria;
        if ($isGetCriteria) {
            return $rtn;
        }
        
        $rtn['data'] = self::model()->findAll($criteria);
        
        return $rtn;
    }
}