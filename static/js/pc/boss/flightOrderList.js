$(function() {
	_$.extend({
		formats: _$.mergeParams(_$.formats, {
			"F_FLIGHT_NO": /^[A-Z0-9]{5,6}$/,
			"F_AIRLINE_CODE": /^[A-Z]{2}$/,
			"F_CRAFT_CODE": /^[A-Z0-9]{3}$/,
			"F_CABIN": /^[A-Z]\d?$/,
			"F_PNR": /^[A-Z0-9]{6}$/,
			"F_TICKET_NO": /^\d{3}-\d{10}$/
		}),
		changeStatusUrl: "/boss/flight/changeStatus",
		changeStatusRequest: function(params) {
			if (typeof (params['_obj']) != "undefined") {
				var obj = params['_obj']; params['_obj'] = null;
				params = _$.mergeParams(_$.changeStatusBaseParams(obj), params);
			}

			var isHandle = parseInt(params["_isHandle"]);
			var statusStr = params["_statusStr"];
			
			var endFunc = "cS2" + statusStr + "End";
			$.post(_$.changeStatusUrl, params, function(data) {
				if (data.rc != 0) {
					var msg = "操作失败(" + data.rc + ")";
					if (isHandle) {
						layer.msg(msg);
					} else {
						_$.createTips(msg);
					}
				} else {
					if (typeof(_$[endFunc]) != "undefined") {
						_$[endFunc]();
					} else {
						_$.reload();
					}
				}
			}, "json");
		},
		changeStatusHtml: function() {
			return '<div class="row"><div class="col-sm-12">是否确定？</div></div>';
		},
		changeStatusGetHtml: function(obj) {
			var rtn = '<div class="row">获取失败！</div>';
			$.ajax({
				type: "GET",
				url: "/boss/flight/getChangeStatusHtml",
				data: _$.changeStatusBaseParams(obj),
				dataType: "json",
				async: false,
				success: function(data) {
					if (!data.rc) {
						rtn = data.data.html;
					}
				}
			});
			
			return rtn;
		},
		changeStatusBaseParams: function(obj) {
			var types = {
				orderID: "data-order-id",
				status: "data-status",
				_statusStr: "data-status-str",
				_isHandle: "data-is-handle"
			};

			var params = {};
			for (var type in types) {
				if (typeof(params[type]) == "undefined") {
					params[type] = obj.attr(types[type]);
				}
			}
			
			return params;
		},
		changeStatusParams: function(obj) {
			var params = false;
			var field = "cS2" + obj.attr("data-status-str") + "_";
			if (!(params = _$.collectParams("*[name^='" + field + "']", field, _$.createTips))) {
				return false;
			}
			
			params = _$.mergeParams(_$.changeStatusBaseParams(obj), params);
			
			return params;
		},
		bindChangeStatusClick: function() {
			$(".c_change_status").unbind("click").click(function() {
				var statusStr = $(this).attr("data-status-str");
				var isHandle = parseInt($(this).attr("data-is-handle"));
				if (isHandle) {
					_$.changeStatusRequest({_obj: $(this)});
				} else {
					var config = {
						"url": _$.changeStatusUrl,
						"htmlFunc": _$.changeStatusHtml,
						"paramsFunc": _$.changeStatusParams,
						"overFunc": _$.reload
					};
					_$.open("cS2" + statusStr, config, $(this));
				}
			});
		},
		cS2BookFailTitle: "出票失败",
		cS2BookFailHtml: function() {
			return '<div class="row"><div class="col-sm-12">是否确定<span class="text-danger">出票失败</span>？</div></div>';
		},
		cS2BookSuccTitle: "出票成功",
		cS2BookSuccHtml: function(obj) {
			return _$.changeStatusGetHtml(obj);
		},
		cS2BookSuccLayerConfig: {
			area: ["500px", "500px"]
		},
		cS2RsnAgreeTitle: "同意改签",
		cS2RsnAgreeHtml: function(obj) {
			return _$.changeStatusGetHtml(obj);
		},
		cS2RsnAgreeLayerConfig: {
			area: ["500px", "500px"]
		},
	});
	
	_$.bindChangeStatusClick();
});