<?php
class SystemController extends ApiController {
    public function actionConfig() {
        if (!F::checkParams($_POST, array('userID' => ParamsFormat::INTNZ))) {
            $this->errAjax(RC::RC_VAR_ERROR);
        }
    
        if (!($user = User::model()->findByPk($_POST['userID']))) {
            $this->errAjax(RC::RC_USER_NOT_EXISTS);
        }
       
        if(isset($_POST['deviceToken'])){
            $this->onAjax($user->setDeviceToken($_POST['deviceToken']));
        }else $this->onAjax(F::corReturn());
    }
}