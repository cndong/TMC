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
        /*
        if (Q::isLocalEnv()) {
            $_POST = array (
              'status' => '1',
              'trainNo' => '6021',
              'arriveStation' => '周家',
              'departStationCode' => 'niujia',
              'sign' => 'dc736ba24338ec396a36504f53f6a1f4',
              'merchantOrderID' => '6',
              'arriveStationCode' => 'zhoujia',
              'departDateTime' => '2016-08-22 12:34',
              'orderID' => 'TAG4D0211900015',
              'passengers' => '[{"type":"1","name":"\\u5355\\u9752\\u82b3","cardType":"1","cardNo":"330124196908282125","seatType":"2","id":"TAG4F0291910018","seatName":"14\\u8f66\\u53a2,06F\\u5ea7","price":"1.0"}]',
              'requestTime' => '1467872323',
              'pickNo' => 'E622851719',
              'departStation' => '牛家',
              'servicePrice' => '1.5',
              'merchantID' => '12',
            );
        }
        */
        
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
}