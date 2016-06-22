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
    
    public function actionModifyUser() {
        if (!F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($_POST['userID']))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
        
        $admin = User::model()->findByPk(Yii::app()->user->id);
        if ($user->companyID != $admin->companyID) {
            $this->errAjax(RC::RC_FORBIDDEN);
        }
        
        if (!F::isCorrect($res = $user->modify($_POST))) {
            $this->onAjax($res);
        }
        
        $this->corAjax();
    }
    
    public function actionAjaxGetModifyUserHtml() {
        if (!F::checkParams($_GET, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
        
        if (!($user = User::model()->findByPk($_GET['userID']))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }

        $admin = User::model()->findByPk(Yii::app()->user->id);
        if ($user->companyID != $admin->companyID) {
            $this->errAjax(RC::RC_FORBIDDEN);
        }
        
        $company = Company::model()->findByPk($admin->companyID);
        $departments = array();
        foreach ($company->departments as $department) {
            $departments[] = F::arrayGetByKeys($department, array('id', 'name'));
        }
        
        $tmp = UserRole::model()->findAll();
        $roles = array();
        foreach ($tmp as $role) {
            $roles[] = F::arrayGetByKeys($role, array('id', 'name'));
        }
        
        $rtn = array(
            'departmentList' => $departments, 
            'roleList' => $roles,
            'user' => F::arrayGetByKeys($user, array('id', 'departmentID', 'name', 'password', 'isReviewer', 'roleIDs')) 
        );
        $rtn['user']['roleIDs'] = explode(',', $rtn['user']['roleIDs']);
        
        $this->corAjax($rtn);
    }
}