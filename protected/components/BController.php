<?php
class BController extends QController {
    public $layout = '//layouts/boss';
    public $loginController = 'index';
    public $loginAction = 'login';
    public $indexUrl = 'index/index';
    public $admin = Null;
    protected $_adminClass = '';
    
    protected function getIndexUrl() {
        return $this->createUrl($this->indexUrl);
    }
    
    protected function getLoginUrl($returnUrl = '') {
        $returnUrl = $returnUrl ? $returnUrl : $this->getIndexUrl();
        return $this->createUrl($this->loginController . '/' . $this->loginAction, array('returnUrl' => base64_encode($returnUrl)));
    }
    
    public function beforeAction($action) {
        if (!Yii::app()->user->id) {
            if (!(strtolower($this->id) == $this->loginController && strtolower($action->id) == $this->loginAction)) {
                $this->redirect($this->getLoginUrl(Yii::app()->request->getUrl()));
            }
        } else {
            $this->admin = call_user_func(array($this->_adminClass, 'readone'), Yii::app()->user->id);
        }
    
        return parent::beforeAction($action);
    }
    
    public function createListView($dataProvider, $config = array()) {
        if (empty($config['itemView'])) {
            $config['itemView'] = '_' . $this->getAction()->id;
        }
        $default = array(
            'dataProvider' => $dataProvider,
            'itemView' => '',
            'htmlOptions' => array('class' => 'panel-body'),
            'ajaxUpdate' => False,
            'cssFile' => Yii::app()->baseUrl . '/static/plugins/bootstrap/css/tables.bootstrap.css',
            'template' => '{items}<div class="row"><div class="col-lg-5">{summary}</div><div class="col-lg-7">{pager}</div></div>',
            'summaryCssClass' => 'dataTables_info',
            'summaryText' => '共 {count} 条记录',
            'emptyText' => '暂无记录',
            'pager' => array(
                'cssFile' => False,
                'header' => '',
                'firstPageLabel' => '首页',
                'prevPageLabel' => '上一页',
                'nextPageLabel' => '下一页',
                'lastPageLabel' => '末页',
                'firstPageCssClass' => 'paginate_button',
                'lastPageCssClass' => 'paginate_button',
                'previousPageCssClass' => 'paginate_button',
                'nextPageCssClass' => 'paginate_button',
                'internalPageCssClass' => 'paginate_button',
                'hiddenPageCssClass' => 'paginate_button hidden',
                'selectedPageCssClass' => 'paginate_button active',
                'htmlOptions' => array('class' => 'pagination')
            ),
            'pagerCssClass' => 'dataTables_paginate paging_simple_numbers'
        );
    
        $this->widget('QListView', CMap::mergeArray($default, $config));
    }
}