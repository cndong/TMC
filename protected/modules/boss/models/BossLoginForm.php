<?php
class BossLoginForm extends CFormModel {
    public $username;
    public $password;
    
    private $_identity;

    public function rules() {
        return array(
            array('username, password', 'required'),
            array('password', 'authenticate'),
        );
    }

    public function attributeLabels() {
        return array(
            'username' => '用户名',
            'password' => '密码',
        );
    }

    public function authenticate($attribute, $params) {
        if(!$this->hasErrors()) {
            $this->_identity = new BossLoginIdentity($this->username, $this->password);
            if(!$this->_identity->authenticate()) {
                $this->addError('password', '错误的用户名或密码!');
            }
        }
    }

    public function login() {
        if($this->_identity === Null) {
            $this->_identity = new BossLoginIdentity($this->name, $this->password);
            $this->_identity->authenticate();
        }

        if($this->_identity->errorCode === BossLoginIdentity::ERROR_NONE) {
            Yii::app()->user->login($this->_identity, 0);
            return True;
        } else {
            return False;
        }
    }
    
}
