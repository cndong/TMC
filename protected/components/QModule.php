<?php
class QModule extends CWebModule {
    public $defaultController = 'index';
    
    public function init() {
        $this->setImport(array(
            $this->id . '.models.*',
            $this->id . '.components.*',
        ));
    
        //需要恢复
        //Yii::app()->errorHandler->errorAction = $this->id . '/index/error';
    }
}