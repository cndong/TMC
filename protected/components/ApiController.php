<?php
class ApiController extends QController {
    public $merchantID = Null;
    
    private function _getCommonParamsFormat() {
        return array(
            'merchantID' => ParamsFormat::M_ID,
            'requestTime' => ParamsFormat::TIMESTAMP,
            'version' => ParamsFormat::API_VERSION,
            'sign' => ParamsFormat::MD5,
        );
    }
    
    private function _checkAuth($params) {
        if (!($params = F::checkParams($params, $this->_getCommonParamsFormat()))) {
            return F::errReturn(RC::RC_VAR_ERROR);
        }
        
        $merchantConfig = Merchant::getMerchants($params['merchantID']);
        if (F::getSignature($_REQUEST, $merchantConfig['key']) != $params['sign']) {
            return F::errReturn(RC::RC_AUTH_ERROR);
        }
        
        $this->merchantID = $params['merchantID'];
        
        return F::corReturn();
    }
    
    private function _isCheckAuth($action) {
        $releaseControllers = array(
            'provider'
        );
        
        $releaseActions = array(
            'index_error'
        );
        
        $controllerID = strtolower($action->controller->id);
        $actionID = strtolower($action->id);
        
        return strcasecmp($actionID, 'public') && !in_array($controllerID, $releaseControllers) && !in_array($controllerID . '_' . $actionID, $releaseActions);
    }
    
    public function beforeAction($action) {
        Q::log($_REQUEST, 'Api.Request');
        
        if ($this->_isCheckAuth($action)) {
            if (!F::isCorrect($res = $this->_checkAuth($_REQUEST))) {
                $this->onAjax($res);
            }
        }
        
        return parent::beforeAction($action);
    }
    
    public function actionError($rc = RC::RC_ERROR, $msg = '') {
        header('HTTP/1.0 200 OK');
        
        $this->errAjax($rc, $msg);
    }
}