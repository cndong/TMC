<?php
class Controller extends CController {
    public $layout = '//layouts/GPC';

    public function corAjax($data = '') {
        return $this->onAjax(Qmy::corReturn($data));
    }

    public function errAjax($rc, $errMsg = '') {
        return $this->onAjax(Qmy::errReturn($rc, $errMsg));
    }

    public function onAjax($response) {
        echo json_encode($response);
        Yii::app()->end();
    }

    public function error($msg, $url = '', $timeout = 3) {
        $this->render('index/error', array('msg' => $msg, 'url' => $url, 'timeout' => $timeout));
        Yii::app()->end();
    }
    
    //重写redner, 兼容手机
    public function render() {
        //判断是否来自手机，如果来自手机则渲染手机页面路径
    }
}