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
        Q::log($_POST, 'TrainMergeRes');
        
        if (!F::checkParams($_POST, array('merchantOrderID' => ParamsFormat::INTNZ, 'status' => ParamsFormat::BOOL, 'passengers' => ParamsFormat::JSON))) {
            $this->_doTrainFail(RC::RC_VAR_ERROR);
        }
        
        if (!($order = TrainOrder::model()->findByPk($_POST['merchantOrderID']))) {
            $this->_doTrainFail(RC::RC_ORDER_NOT_EXISTS);
        }
        
        $status = $_POST['status'] ? TrainStatus::BOOK_SUCC : ($order->isPrivate ? TrainStatus::BOOK_FAIL_WAIT_RFD : TrainStatus::BOOK_FAIL);
        $_POST['passengers'] = json_decode($_POST['passengers'], True);
        if (!F::isCorrect($res = $order->changeStatus($status, $_POST))) {
            $this->_doTrainFail($res);
        }
        
        $this->_doTrainSucc();
    }
    
    public function actionTrainRefundRes() {
        Q::log($_POST, 'TrainRefundRes');
        
        if (!F::checkParams($_POST, array('merchantOrderID' => ParamsFormat::INTNZ, 'status' => ParamsFormat::BOOL, 'passengers' => ParamsFormat::JSON))) {
            $this->_doTrainFail(RC::RC_VAR_ERROR);
        }

        if (!($order = TrainOrder::model()->findByPk($_POST['merchantOrderID']))) {
            $this->_doTrainFail(RC::RC_ORDER_NOT_EXISTS);
        }
    
        $status = $_POST['status'] ? TrainStatus::RFD_AGREE : TrainStatus::RFD_REFUSE;
        $passengers = json_decode($_POST['passengers'], True);
        if (count($passengers) <= 0 || !($passenger = F::checkParams($passengers[0], array('id' => ParamsFormat::ALNUM, 'refundPrice' => ParamsFormat::FLOAT)))) {
            $this->_doTrainFail(RC::RC_VAR_ERROR);
        }
        
        if (!($ticket = TrainTicket::model()->findByAttributes(array('providerPassengerID' => $passenger['id'])))) {
            $this->_doTrainFail(RC::RC_VAR_ERROR);
        }
        
        if (!F::isCorrect($res = $order->changeStatus($status, array('ticketID' => $ticket->id, 'refundPrice' => $passenger['refundPrice'] * 100)))) {
            $this->_doTrainFail($res);
        }
    
        $this->_doTrainSucc();
    }
}