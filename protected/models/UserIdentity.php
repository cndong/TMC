<?php
class UserIdentity extends CUserIdentity {
    private $_id;
    public $mobile;

    public function __construct($mobile, $password) {
        $this->mobile = $mobile;

        return parent::__construct($mobile, $password);
    }

    public function authenticate() {
        $criteria = new CDbCriteria();
        $criteria->compare('mobile', $this->mobile);
        $user = User::model()->find($criteria);
        if (!$user) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } else if (!$user->validatePassword($this->password)) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else {
            $this->_id = $user->id;
            $this->errorCode = self::ERROR_NONE;
        }

        return !$this->errorCode;
    }

    public function getId() {
        return $this->_id;
    }
}