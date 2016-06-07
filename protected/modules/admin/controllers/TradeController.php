<?php
class TradeController extends AdminController {
    public function actionTradeList() {
        $criteria = new CDbCriteria();
        $criteria->compare('companyID', $this->admin->companyID);
        $criteria->order = 'id DESC';
        $dataProvider = new CActiveDataProvider('CompanyFinanceLog', array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        
        $this->setRenderParams('breadCrumbs', array('交易管理', '交易记录'));
        $this->render('tradeList', array('dataProvider' => $dataProvider));
    }
}