<?php
class BossLoginIdentity extends CUserIdentity {
    private $_id;
    public $username;
    public $password;

    public function __construct($username, $password) {
        $this->_id = $username;
        $this->username = $username;
        $this->password = $password;
        
        return parent::__construct($username, $password);
    }
    
    public function authenticate() {
        $criteria = new CDbCriteria();
        $criteria->compare('username', $this->username);
        $bossAdmin = BossAdmin::model()->find($criteria);
        if (!$bossAdmin) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else if (!$bossAdmin->validatePassword($this->password)) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else {
            $this->_id = $bossAdmin->id;
            $this->errorCode = self::ERROR_NONE;
        }
        
        return !$this->errorCode;
    }
    
    public function getId() {
        return $this->_id;
    }
}
