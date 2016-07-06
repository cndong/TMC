<?php
class ProviderController extends ApiController {
    private function _doTrainSucc() {
        $this->end('succeed');
    }
    
    private function _doTrainFail($msg = '', $data = array()) {
        if ($msg) {
            $msg .= '|' . json_encode($data);
        }
    
        Q::log($msg);
        $this->end('failed');
    }
    
    public function actionTrainMergeRes() {
        if (!F::checkParams($_POST, array('merchantOrderID' => ParamsFormat::INTNZ, 'status' => ParamsFormat::BOOL))) {
            $this->_doTrainFail(RC::RC_VAR_ERROR);
        }
        
        if (!($order = TrainOrder::model()->findByPk($_POST['merchantOrderID']))) {
            $this->_doTrainFail(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $status = $_POST['status'] ? TrainStatus::BOOK_SUCC : ($order->isPrivate ? TrainStatus::BOOK_FAIL_WAIT_RFD : TrainStatus::BOOK_FAIL);
        if (!F::isCorrect($res = $order->changeStatus($status, $_POST))) {
            $this->_doTrainFail($res);
        }
        
        $this->_doTrainSucc();
    }
}