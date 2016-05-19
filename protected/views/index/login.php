<?php
$this->registerJqueryJs();
$this->registerQmyJs();
$this->registerAwesomeCss();
?>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title text-center">登录TMC管理系统</h3>
                </div>
                <div class="panel-body">
                    <fieldset>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user fa-fw"></i></span>
                                <input class="form-control" placeholder="用户名" name="login_username" type="text" data-format="!ALNUMZ|ALNUMNZ" data-err="请填写用户名|用户名错误" autofocus />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
                                <input class="form-control" placeholder="密码" name="login_password" type="password" data-format="!TEXTZ|TEXTNZ" data-err="请填写密码|密码错误" />
                            </div>
                        </div>
                        <!--div class="checkbox">
                            <label>
                                <input name="remember" type="checkbox" value="Remember Me">Remember Me
                            </label>
                        </div-->
                        <div class="form-group">
                            <button class="c_login form-control btn btn-md btn-success btn-block">登录</button>
                        </div>
                        <div class="text-center">
                            <span class="m_login text-danger"></span>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
	$(function() {
		_$.extend({
			loginModifyMsg: function(data) {
				var msg = data;
				if (_$.isObject(data) || _$.isArray(data)) {
					msg = data["_errMsg"];
				}
				$(".m_login").html(msg);
			}
		});
		$(".c_login").click(function() {
			var field = 'login_';
			var params = _$.collectParams("input[name^='" + field + "']", field, _$.loginModifyMsg);
			if (params) {
				$.post("<?php echo Yii::app()->request->getUrl(); ?>", params, function(data) {
					if (!data.rc) {
						_$.go2url(data.url);
					} else {
						_$.loginModifyMsg(data.msg);
					}
				}, "json");
			}
		});
	});
</script>