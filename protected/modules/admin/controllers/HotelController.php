<?php
class HotelController extends AdminController {
   public function actionOrderList() {
        $_GET['companyID'] = $this->admin->company->id;
        $_GET['isPrivate'] = Dict::STATUS_FALSE;
        $data = HotelOrder::search($_GET, True);
        $dataProvider = new CActiveDataProvider('HotelOrder', array(
            'criteria' => $data['criteria'],
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        
        $this->setRenderParams('breadCrumbs', array('酒店', '订单列表'));
        $this->render('orderList', array('dataProvider' => $dataProvider, 'params' => $data['params']));
    }
}