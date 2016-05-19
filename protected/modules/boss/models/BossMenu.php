<?php
class BossMenu extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
    public function tableName() {
        return '{{bossMenu}}';
    }

    public function rules() {
        return array(
            array('parentID, name', 'required'),
            array('parentID, sort, deleted, ctime, utime', 'numerical', 'integerOnly' => True),
            array('name, controller, action, icon', 'length', 'max' => 25),
            array('path', 'length', 'max' => 50),
            array('id, name, controller, action, icon, parentID, path, sort, deleted, ctime, utime', 'safe', 'on' => 'search'),
        );
    }
    
    private function _getCreateMenuFormats() {
        return array(
            'name' => ParamsFormat::TEXTNZ,
            'parentID' => '!' . ParamsFormat::INTNZ . '--0',
            'controller' => '!' . ParamsFormat::ALNUM . '--',
            'action' => '!' . ParamsFormat::ALNUM . '--',
            'icon' => '!' . ParamsFormat::ALNUMXX . '--',
            'sort' => '!' . ParamsFormat::INTNZ . '--0',
            'isHide' => '!' . ParamsFormat::BOOL . '--0'
        );
    }
    
    public function createMenu($params) {
        if (!($params = F::checkParams($params, $this->_getCreateMenuFormats()))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        if ($params['parentID'] && !($parentMenu = BossMenu::readone($params['parentID']))) {
            return F::errReturn(RC::RC_BOSS_MENU_NOT_EXISTS);
        }
        
        $params['path'] = $params['parentID'] ? '' : ltrim($parentMenu->path . ',' . $parentMenu->id, ',');
        
        $menu = new BossMenu();
        $menu->attributes  = $params;
        if (!$menu->save()) {
            Q::log(var_export($menu->getErrors(), True), 'dberror.BossMenu');
            return F::errReturn(RC::RC_DB_ERROR);
        }
        
        return F::corReturn($menu);
    }
}