<?php
class UserRole extends QActiveRecord {
    const BOSS_ADMIN_ID = 1;
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function tableName() {
        return '{{userRole}}';
    }

    public function rules() {
        return array(
            array('name', 'required'),
            array('deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('name', 'length', 'max' => 25),
            array('menuIDs', 'length', 'max' => 255),
            array('id, name, menuIDs, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    public function getMenusByPath($path, $menus) {
        if (count($path) == 1) {
            return array(strval($path[0]) => $menus[$path[0]]);
        }
        
        $rtn = array(strval($path[0]) => $menus[$path[0]]);
        $rtn[strval($path[0])]['subMenus'] = $this->getMenusByPath(array_slice($path, 1), $menus);
        
        return $rtn;
    }
    
    public function getMenus() {
        $where = $this->id == self::BOSS_ADMIN_ID ? '' : "WHERE id IN({$this->menuIDs})";
        $sql = 'SELECT id,name,controller,action,icon,parentID,sort, isHide,TRIM(LEADING "," FROM CONCAT(path,",",id)) as nPath FROM ' . UserMenu::model()->tableName() . " {$where} ORDER BY path,sort";
        if (!($res = Yii::app()->db->createCommand($sql)->queryAll())) {
            return array();
        }
        
        $menus = array();
        foreach ($res as $menu) {
            $menus[$menu['id']] = $menu; 
        }
        
        $rtn = array();
        foreach ($menus as $k => $menu) {
            $rtn = F::mergeArrayInt($rtn, $this->getMenusByPath(explode(',', $menu['nPath']), $menus));
        }
        
        return $rtn;
    }
}