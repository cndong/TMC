<?php
class BossAdmin extends QActiveRecord {
    const PASSWORD_SALT = 'd6a4s!@dSx,/a7$AW;yYh';
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{bossAdmin}}';
    }

    public function rules() {
        return array(
            array('username, password, nickname, mobile, roleIDs', 'required'),
            array('deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('username', 'length', 'max' => 25),
            array('password', 'length', 'max' => 32),
            array('nickname', 'length', 'max' => 50),
            array('mobile', 'length', 'max' => 11),
            array('roleIDs', 'length', 'max' => 255),
            array('id, username, password, nickname, mobile, roleIDs, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function validatePassword($password) {
        return self::getHashPassword($password, $this->ctime) === $this->password;
    }
    
    public static function getHashPassword($password, $adminSalt) {
        return md5($password . $adminSalt . self::PASSWORD_SALT);
    }
    
    public function getRoles() {
        $criteria = new CDbCriteria();
        $criteria->addInCondition('id', explode(',', $this->roleIDs));
         
        $roles = BossRole::model()->findAll($criteria);
        $roles = F::arrayAddField($roles, 'id');
        return $roles ? $roles : array();
    }
    
    public function getMenus($isReload = True) {
        $cacheKey = KeyManager::getBossMenusKey($this->username);
        if (!$isReload && ($menus = Yii::app()->cache->get($cacheKey))) {
            return $menus;
        }
        
        $rtn = array();
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            $rtn = F::mergeArrayInt($rtn, $role->getMenus());
        }
        
        $this->sortMenus($rtn);
        Yii::app()->cache->set($cacheKey, $rtn, 30 * 60);
        return $rtn;
    }
    
    public function sortMenusFunc($a, $b) {
        return $a['sort'] > $b['sort'];
    }
    
    public function sortMenus(&$menus) {
        usort($menus, array($this, 'sortMenusFunc'));
        foreach ($menus as &$menu) {
            if (!empty($menu['subMenus'])) {
                $menu['subMenus'] = $this->sortMenus($menu['subMenus']);
            }
        }
         
        return $menus;
    }
}