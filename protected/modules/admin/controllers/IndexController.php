<?php
class IndexController extends AdminController {
    public function actionLogin() {
        if (Yii::app()->request->isAjaxRequest && !empty($_POST)) {
            if (empty($_POST['username']) || empty($_POST['password'])) {
                $this->errAjax(RC::RC_VAR_ERROR, '手机号或密码不能为空');
            }
            
            $identity = new UserIdentity($_POST['username'], $_POST['password']);
            if (!$identity->authenticate()) {
                $this->errAjax(RC::RC_LOGIN_FAILED, '用户名或密码错误');
            }
            
            Yii::app()->user->login($identity);
            
            $returnUrl = F::getQuery('returnUrl');
            $returnUrl = $returnUrl ? base64_decode($returnUrl) : $this->getIndexUrl();
            
            $this->corAjax(array('url' => $returnUrl));
        }
        
        if (Yii::app()->user->id) {
            $this->redirect($this->getIndexUrl());
        }
        
        $this->render('//index/login');
    }
    
    public function actionLogout() {
        Yii::app()->user->logout();
        $returnUrl = Yii::app()->request->urlReferrer ? Yii::app()->request->urlReferrer : '';
        $this->redirect($this->getLoginUrl($returnUrl));
    }
    
    public function actionIndex() {
        $this->setRenderParams('containerClass', '');
        $this->render('//index/index', array('menus' => $this->_getLeftHtml($this->admin->getMenus(), True)));
    }
    
    private function _getLeftSearchHtml() {
        return <<<EOF
            <li class="sidebar-search">
            <div class="input-group custom-search-form">
                <input type="text" placeholder="搜索..." class="form-control">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
            </div>
EOF;
    }
    
    private function _getLeftHtml($menus, $isTop = False) {
        $rtn = '';
    
        $ulClass = $isTop ? 'nav' : 'nav nav-sub';
        $display = $isTop ? '' : ' collapse';
        $rtn .= "<ul class='{$ulClass}{$display}' id='side-menu'>";
        if ($isTop) {
            $rtn .= $this->_getLeftSearchHtml();
        }
            
        foreach ($menus as $menu) {
            if ($menu['isHide']) {
                continue;
            }
            
            $href = !empty($menu['controller']) && !empty($menu['action']) ? $this->createUrl("{$menu['controller']}/{$menu['action']}") : 'javascript:;';
            $target = !empty($menu['controller']) && !empty($menu['action']) ? 'right' : '_self';
            $iconClass = !empty($menu['icon']) ? $menu['icon'] : 'fa-bar-chart-o';
            $arrowClass = empty($menu['subMenus']) ? '' : 'fa arrow';
            
            $rtn .= '<li>';
            $rtn .= "<a href='{$href}' target='{$target}'>";
            $rtn .= "<i class='fa {$iconClass} fa-fw'></i>";
            $rtn .= "<span class='title'>{$menu['name']}</span>";
            $rtn .= "<span class='{$arrowClass}'></span>";
            $rtn .= '</a>';
    
            if (!empty($menu['subMenus'])) {
                $rtn .= $this->_getLeftHtml($menu['subMenus'], False);
            }
    
            $rtn .= '</li>';
        }
        
        $rtn .= '</ul>';
    
        return $rtn;
    }
    
    public function actionRight() {
        $this->render('right');
    }
}