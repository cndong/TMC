<?php
class TestController extends QController {
    public function actionBookPush($orderID) {
        $order = TrainOrder::model()->findByPk($orderID);
        var_dump($order->changeStatus(TrainStatus::BOOK_PUSHED, array('operaterID' => Dict::OPERATER_SYSTEM)));
    }
}