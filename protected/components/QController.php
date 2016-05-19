<?php
class QController extends CController {
    public $layout = '//layouts/GPC';
    protected $_renderParams = array();
    
    public function setRenderParams($k, $v = '') {
        if (is_array($k)) {
            $this->_renderParams = CMap::mergeArray($this->_renderParams, $k);
        } else {
            if (is_array($v)) {
                $this->_renderParams[$k] = CMap::mergeArray(empty($this->_renderParams[$k]) ? array() : $this->_renderParams[$k], $v);
            } else {
                $this->_renderParams[$k] = $v;
            }
        }
    }
    
    public function getRenderParams($k, $default = Null) {
        return isset($this->_renderParams[$k]) ? $this->_renderParams[$k] : $default;
    }

    public function corAjax($data = '', $msg = '') {
        return $this->onAjax(F::corReturn($data, $msg));
    }

    public function errAjax($rc, $errMsg = '') {
        return $this->onAjax(F::errReturn($rc, $errMsg));
    }

    public function onAjax($res) {
        if (isset($res['data']) && empty($res['data'])) {
            $res['data'] = new stdClass();
        }
        
        if ($res['rc'] != RC::RC_SUCCESS && empty($res['msg'])) {
            $res['msg'] = RC::getMsg($res['rc']);
        }
        
        echo json_encode($res);
        Yii::app()->end();
    }

    public function end($text) {
        echo $text;
        Yii::app()->end();
    }
    
    public function error($msg, $url = '', $timeout = 3) {
        $this->render('/index/error', array('msg' => $msg, 'url' => $url, 'timeout' => $timeout));
        Yii::app()->end();
    }
    
    public function registerFile($file, $isScript = True, $position = Null) {
        $file = Yii::app()->baseUrl . '/static/' . $file;
        if ($isScript) {
            Yii::app()->clientScript->registerScriptFile($file, $position);
        } else {
            Yii::app()->clientScript->registerCssFile($file);
        }
    }
    
    public function registerBaseCss() {
        $this->registerFile('plugins/bootstrap/css/bootstrap.min.css', False);
        $this->registerFile('css/pc/boss/sb-admin-2.css', False);
    }
    
    public function registerAwesomeCss() {
        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/static/plugins/font-awesome/css/font-awesome.min.css', False);
    }
    
    public function registerJqueryJs() {
        $this->registerFile('plugins/jquery/jquery-2.1.4.min.js');
    }
    
    public function registerBootstrapJs() {
        $this->registerFile('plugins/bootstrap/js/bootstrap.min.js');
    }
    
    public function registerQmyJs() {
        $this->registerFile('js/common/qmy.js');
    }
    
    public function registerDatePickerJs() {
        $this->registerFile('plugins/datepicker/WdatePicker.js');
    }
    
    public function registerLayerJs() {
        $this->registerFile('plugins/layer/layer.js');
    }
    
    public function registerLayerExtJs() {
        $this->registerFile('plugins/layer/extend/layer.ext.js');
    }
    
    public function registerActionJs() {
        $this->registerFile('js/pc/' . $this->getModule()->id . '/' . $this->id . ucfirst($this->getAction()->id) . '.js', True, CClientScript::POS_END);
    }
    
    public function registerControllerJs() {
        $this->registerFile('js/pc/' . $this->getModule()->id . '/' . $this->id . '.js', True, CClientScript::POS_END);
    }
    
    /*
    //重写redner, 兼容手机
    public function render() {
        //判断是否来自手机，如果来自手机则渲染手机页面路径
    }
    */
}