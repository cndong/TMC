<?php
class TradeController extends AdminController {
    public function actionTradeList() {
        $searchTypes = array(
            'userID' => '用户ID',
            'departmentID' => '部门ID'
        );
        $searchParams = $_GET;
        $searchParams['searchType'] = !empty($searchParams['searchType']) && isset($searchTypes[$searchParams['searchType']]) ? $searchParams['searchType'] : False;
        if ($searchParams['searchType']) {
            $searchParams[$searchParams['searchType']] = empty($searchParams['searchValue']) ? '' : $searchParams['searchValue'];
        }
        $searchParams['companyID'] = $this->admin->companyID;
        
        $data = CompanyFinanceLog::search($searchParams, True);
        $dataProvider = new CActiveDataProvider('CompanyFinanceLog', array(
            'criteria' => $data['criteria'],
            'pagination' => array(
                'pageSize' => 10,
            )
        ));
        
        $this->setRenderParams('breadCrumbs', array('交易管理', '交易记录'));
        $this->render('tradeList', array('dataProvider' => $dataProvider, 'params' => $data['params'], 'searchTypes' => $searchTypes));
    }
}