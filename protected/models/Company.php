<?php
class Company extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return '{{company}}';
    }

    public function rules() {
        return array(
            array('name', 'required'),
            array('deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('name', 'length', 'max' => 90),
            array('id, name, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function relations() {
        return array(
            'departments' => array(self::HAS_MANY, 'Department', 'companyID')
        );
    }
    
    private static function _getCreateCompnyFormats() {
        return array(
            'name' => ParamsFormat::TEXTNZ
        );
    }
    
    private static function _getCreateCompanyParams($params) {
        if (!($params = F::checkParams($params, self::_getCreateCompnyFormats()))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        if (Company::model()->findByAttributes(array('name' => $params['name']))) {
            return F::errReturn(RC::RC_COM_HAD_EXISTS);
        }
        
        return F::corReturn($params);
    }
    
    public static function createCompany($params) {
        if (!F::isCorrect($res = self::_getCreateCompanyParams($params))) {
            return $res;
        }
        
        $company = new Company();
        $company->attributes = $res['data'];
        if (!$company->save()) {
            Q::log('----------------');
            Q::log($company->getErrors(), 'dberror.createCompany');
            Q::log($res['data'], 'dberror.createCompany');
            Q::log('----------------');
            
            return F::errReturn(RC::RC_COM_CREATE_ERROR);
        }
        
        return F::corReturn($company);
    }
    
    public static function search($params, $isGetCriteria = False) {
        $rtn = array('criteria' => Null, 'params' => array(), 'data' => array());
        
        $rtn['params'] = $params = F::checkParams($params, array('search' => '!' . ParamsFormat::TEXTNZ . '--', 'sort' => '!' . ParamsFormat::ALNUM . '--id', 'sortDirection' => '!' . ParamsFormat::SORT_DIRECTION . '--DESC'));
        
        $criteria = new CDbCriteria();
        $criteria->order = $params['sort'] . ' ' . $params['sortDirection'];
        if ($params['search']) {
            $criteria->compare('id', $params['search']);
            $criteria->addSearchCondition('name', $params['search'], True, 'OR');
        }
        
        $rtn['criteria'] = $criteria;
        if ($isGetCriteria) {
            return $rtn;
        }
        
        $rtn['data'] = self::model()->findAll($criteria);
        
        return $rtn;
    }
}