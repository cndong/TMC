<?php
class SystemController extends BossController {
    public function actionChangePassword() {
        $isSucc = False;
        $msg = '';
        
        if (Yii::app()->request->isPostRequest) {
            if (empty($_POST['password']) || empty($_POST['repassword']) || $_POST['password'] != $_POST['repassword']) {
                $msg = '参数错误';
            }
            
            if (!$msg) {
                $this->bossAdmin->password = BossAdmin::getHashPassword($_POST['password'], $this->bossAdmin->ctime);
                if (!$this->bossAdmin->save()) {
                    $msg = '未知错误';
                } else {
                    $isSucc = True;
                    $msg = '修改成功';
                }
            }
        }
        
        $this->render('changePassword', array('isSucc' => $isSucc, 'msg' => $msg));
    }
}