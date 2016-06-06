<?php 
    $this->registerAwesomeCss();
    $this->registerJqueryJs();
    $this->registerBootstrapJs();
    $this->registerFile('plugins/metisMenu/metisMenu.min.js', False);
    $this->registerFile('plugins/metisMenu/metisMenu.min.js');
?>
<nav style="margin-bottom: 0" role="navigation" class="navbar navbar-default navbar-static-top active">
    <div class="navbar-header">
        <button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a href="javascript:;" class="navbar-brand">十分便民</a>
    </div>
    <ul class="nav navbar-top-links navbar-right in">
        <!--li class="dropdown active">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle active">
                <i class="fa fa-envelope fa-fw"></i>  <i class="fa fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu dropdown-messages in">
                <li>
                    <a href="#" class="active">
                        <div>
                            <strong>John Smith</strong>
                            <span class="pull-right text-muted">
                                <em>Yesterday</em>
                            </span>
                        </div>
                        <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="active">
                        <div>
                            <strong>John Smith</strong>
                            <span class="pull-right text-muted">
                                <em>Yesterday</em>
                            </span>
                        </div>
                        <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="active">
                        <div>
                            <strong>John Smith</strong>
                            <span class="pull-right text-muted">
                                <em>Yesterday</em>
                            </span>
                        </div>
                        <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="text-center active">
                        <strong>Read All Messages</strong>
                        <i class="fa fa-angle-right"></i>
                    </a>
                </li>
            </ul>
        </li>
        <li class="dropdown active">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle active">
                <i class="fa fa-tasks fa-fw"></i>  <i class="fa fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu dropdown-tasks in">
                <li>
                    <a href="#" class="active">
                        <div>
                            <p>
                                <strong>Task 1</strong>
                                <span class="pull-right text-muted">40% Complete</span>
                            </p>
                            <div class="progress progress-striped active">
                                <div style="width: 40%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="40" role="progressbar" class="progress-bar progress-bar-success">
                                    <span class="sr-only">40% Complete (success)</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="active">
                        <div>
                            <p>
                                <strong>Task 2</strong>
                                <span class="pull-right text-muted">20% Complete</span>
                            </p>
                            <div class="progress progress-striped active">
                                <div style="width: 20%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="20" role="progressbar" class="progress-bar progress-bar-info">
                                    <span class="sr-only">20% Complete</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="active">
                        <div>
                            <p>
                                <strong>Task 3</strong>
                                <span class="pull-right text-muted">60% Complete</span>
                            </p>
                            <div class="progress progress-striped active">
                                <div style="width: 60%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="60" role="progressbar" class="progress-bar progress-bar-warning">
                                    <span class="sr-only">60% Complete (warning)</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="active">
                        <div>
                            <p>
                                <strong>Task 4</strong>
                                <span class="pull-right text-muted">80% Complete</span>
                            </p>
                            <div class="progress progress-striped active">
                                <div style="width: 80%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="80" role="progressbar" class="progress-bar progress-bar-danger">
                                    <span class="sr-only">80% Complete (danger)</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="text-center active">
                        <strong>See All Tasks</strong>
                        <i class="fa fa-angle-right"></i>
                    </a>
                </li>
            </ul>
        </li>
        <li class="dropdown active">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle active">
                <i class="fa fa-bell fa-fw"></i>  <i class="fa fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu dropdown-alerts in">
                <li>
                    <a href="#" class="active">
                        <div>
                            <i class="fa fa-comment fa-fw"></i> New Comment
                            <span class="pull-right text-muted small">4 minutes ago</span>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="active">
                        <div>
                            <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                            <span class="pull-right text-muted small">12 minutes ago</span>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="active">
                        <div>
                            <i class="fa fa-envelope fa-fw"></i> Message Sent
                            <span class="pull-right text-muted small">4 minutes ago</span>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="active">
                        <div>
                            <i class="fa fa-tasks fa-fw"></i> New Task
                            <span class="pull-right text-muted small">4 minutes ago</span>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="active">
                        <div>
                            <i class="fa fa-upload fa-fw"></i> Server Rebooted
                            <span class="pull-right text-muted small">4 minutes ago</span>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" class="text-center active">
                        <strong>See All Alerts</strong>
                        <i class="fa fa-angle-right"></i>
                    </a>
                </li>
            </ul>
        </li-->
        <li class="dropdown active">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle active">
                <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu dropdown-user in">
                <li>
                    <a href="javascript:;" class="active"><i class="fa fa-user fa-fw"></i><?php echo isset($this->admin->nickname) ? $this->admin->nickname : $this->admin->name; ?></a>
                </li>
                <!--li>
                    <a href="javascript:;" class="active"><i class="fa fa-gear fa-fw"></i>用户中心</a>
                </li-->
                <li class="divider"></li>
                <li>
                    <a href="<?php echo $this->createUrl('logout'); ?>"><i class="fa fa-sign-out fa-fw"></i>退出</a>
                </li>
            </ul>
        </li>
    </ul>
    <div class="navbar-default sidebar" id="page-naver">
        <div class="sidebar-nav navbar-collapse">
            <?php echo $menus; ?>
        </div>
    </div>
</nav>
<div id="page-wrapper">
	<iframe id="right" name="right" scrolling="no" width="100%" src="<?php echo $this->createUrl('right'); ?>" frameborder="0"></iframe>
</div>
<script type="text/javascript">
    $(function() {
		$('#side-menu').metisMenu();
		
	    $(window).bind("load resize", function() {
	        topOffset = 50;
	        width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
	        if (width < 768) {
	            $('div.navbar-collapse').addClass('collapse');
	            topOffset = 100; // 2-row-menu
	        } else {
	            $('div.navbar-collapse').removeClass('collapse');
	        }

	        height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
	        height = height - topOffset;
	        if (height < 1) height = 1;
	        if (height > topOffset) {
	            $("#page-wrapper, #page-naver, #right").css("min-height", (height) + "px");
	        }
	    });

	    var url = window.location;
	    var element = $('ul.nav a').filter(function() {
	        return this.href == url || url.href.indexOf(this.href) == 0;
	    }).addClass('active').parent().parent().addClass('in').parent();
	    if (element.is('li')) {
	        element.addClass('active');
	    }
    });
</script>