<?php
class CompanyController extends AdminController {
    public function actionCompanyDetail() {
        $company = Company::model()->findByPk($this->admin->companyID);
        $departmentNum = count($company->departments);
        $userNum = count($company->users);
        
        $this->setRenderParams('breadCrumbs', array('企业管理', '企业信息'));
        $this->render('companyDetail', array('company' => $company, 'departmentNum' => $departmentNum, 'userNum' => $userNum));
    }
    
    public function actionDepartmentList() {
        $_GET['companyID'] = $this->admin->companyID;
        $data = Department::search($_GET, True);
        $dataProvider = new CActiveDataProvider('Department', array(
            'criteria' => $data['criteria'],
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        $companies = F::arrayAddField(Company::model()->findAll(array('select' => array('id', 'name'))), 'id');
        
        $this->setRenderParams('breadCrumbs', array('企业管理', '部门列表'));
        $this->render('departmentList', array('dataProvider' => $dataProvider, 'params' => $data['params']));
    }
    
    public function actionUserList() {
        $_GET['companyID'] = $this->admin->companyID;
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
    
    public function actionAjaxDepartmentList() {
        $rtn = array();
        
        $departments = Department::model()->findAllByAttributes(array('companyID' => $this->admin->companyID), 'deleted=:deleted', array(':deleted' => Department::DELETED_F));
        foreach ($departments as $department) {
            $rtn[] = F::arrayGetByKeys($department, array('id', 'name'));
        }
        
        $this->corAjax(array('departmentList' => $rtn));
    }
    
    public function actionAjaxUserRoleList() {
        $rtn = array();
        $roles = UserRole::model()->findAll('deleted=:deleted', array(':deleted' => UserRole::DELETED_F));
        foreach ($roles as $role) {
            $rtn[] = F::arrayGetByKeys($role, array('id', 'name'));
        }
    
        $this->corAjax(array('userRoleList' => $rtn));
    }
    
    public function actionCreateDepartment() {
        $_POST['companyID'] = $this->admin->companyID;
        if (F::isCorrect($res = Department::createDepartment($_POST))) {
            $this->corAjax(array('id' => $res['data']->id));
        }
        
        $this->onAjax($res);
    }
    
    public function actionCreateUser() {
        $_POST['companyID'] = $this->admin->companyID;
        if (F::isCorrect($res = User::createUser($_POST))) {
            $this->corAjax(array('id' => $res['data']->id));
        }
        
        $this->onAjax($res);
    }
}