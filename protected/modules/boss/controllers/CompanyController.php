<?php
class CompanyController extends BossController {
    public function actionCreateCompany() {
        if (F::isCorrect($res = Company::createCompany($_POST))) {
            $this->corAjax(array('id' => $res['data']->id));
        }
        
        $this->onAjax($res);
    }
    
    public function actionCreateDepartment() {
        if (F::isCorrect($res = Department::createDepartment($_POST))) {
            $this->corAjax(array('id' => $res['data']->id));
        }
        
        $this->onAjax($res);
    }
    
    public function actionCreateUser() {
        if (F::isCorrect($res = User::createUser($_POST))) {
            $this->corAjax(array('id' => $res['data']->id));
        }
        
        $this->onAjax($res);
    }
    
    public function actionAjaxToggleReviewer() {
        if (!($params = F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ)))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($params['userID'], 'deleted=:deleted', array(':deleted' => User::DELETED_F)))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        $this->onAjax($user->toggleRewiewer());
    }
    
    public function actionAjaxCompanyList() {
        $rtn = array();
        $companies = Company::model()->findAll();
        foreach ($companies as $company) {
            $rtn[] = F::arrayGetByKeys($company, array('id', 'name'));
        }
        
        $this->corAjax(array('companyList' => $rtn));
    }
    
    public function actionAjaxDepartmentList() {
        if (!F::checkParams($_GET, array('companyID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        $rtn = array();
        $departments = Department::model()->findAllByAttributes(array('companyID' => $_GET['companyID']));
        foreach ($departments as $department) {
            $rtn[] = F::arrayGetByKeys($department, array('id', 'name'));
        }
    
        $this->corAjax(array('departmentList' => $rtn));
    }
    
    public function actionCompanyList() {
        $data = Company::search($_GET, True);
        $dataProvider = new CActiveDataProvider('Company', array(
            'criteria' => $data['criteria'],
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        
        $this->setRenderParams('breadCrumbs', array('企业管理', '企业列表'));
        $this->render('companyList', array('dataProvider' => $dataProvider, 'params' => $data['params']));
    }
    
    public function actionDepartmentList() {
        $data = Department::search($_GET, True);
        $dataProvider = new CActiveDataProvider('Department', array(
            'criteria' => $data['criteria'],
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        $companies = F::arrayAddField(Company::model()->findAll(array('select' => array('id', 'name'))), 'id');
        
        $this->setRenderParams('breadCrumbs', array('企业管理', '部门列表'));
        $this->render('departmentList', array('dataProvider' => $dataProvider, 'params' => $data['params'], 'companies' => $companies));
    }
    
    public function actionUserList() {
        $data = User::search($_GET, True);
        $dataProvider = new CActiveDataProvider('User', array(
            'criteria' => $data['criteria'],
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        
        $this->setRenderParams('breadCrumbs', array('企业管理', '员工列表'));
        $this->render('userList', array('dataProvider' => $dataProvider, 'params' => $data['params']));
    }
}