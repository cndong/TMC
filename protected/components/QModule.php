<?php
class QModule extends CWebModule {
    public $defaultController = 'index';
    protected $_cookieKey = 'qmy';
    
    public function init() {
        $this->setImport(array(
            $this->id . '.models.*',
            $this->id . '.components.*',
        ));
    
        Yii::app()->user->setStateKeyPrefix($this->_cookieKey);
        Yii::app()->user->init();
        //需要恢复
        //Yii::app()->errorHandler->errorAction = $this->id . '/index/error';
    }
}