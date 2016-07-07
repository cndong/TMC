<?php
class TestController extends QController {
    public function actionBookPush($orderID) {
        $order = TrainOrder::model()->findByPk($orderID);
        var_dump($order->changeStatus(TrainStatus::BOOK_PUSHED));
    }
}