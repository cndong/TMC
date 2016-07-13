$(function() {
	_$.extend({
		changeStatusUrl: "/boss/train/changeStatus",
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
						if (!isHandle) {
							_$.reload();
						} else {
							$("[name='searchType']").val("orderID");
							$("[name='searchValue']").val(params["orderID"]);
							$("[name='searchForm']").submit();
						}
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
				url: "/boss/train/getChangeStatusHtml",
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
		cS2RfdedTitle: "退款成功",
		cS2RfdedHtml: function(obj) {
			return _$.changeStatusGetHtml(obj);
		},
		cS2RfdedShow: function(obj) {
			$(".c_select_ticket").change(function() {
				var ticketID = $(this).attr("data-ticket-id");
				var rowID = 'cS2RsnAgreeRow_' + ticketID;
				var passenger = $(this).attr("data-passenger-name");
				var isChecked = $(this).prop("checked");
				if (isChecked) {
					var html = '<div class="row row-form-margin" id="' + rowID + '"><div class="col-sm-4 text-right">' + passenger + '</div><div class="col-sm-6 form-inline">';
					html += '<div class="form-group form-group-sm"> <input type="text" name="cS2Rfded_tickets[' + ticketID + ']" data-format="FLOAT" data-err="' + passenger + '金额错误" value="' + $(this).attr("data-refund-price") + '" class="form-control" size="5" /> </div>';
					html += '</div></div>';
					$(this).parents(".row").parent().append(html);
				} else {
					$("#" + rowID).remove();
				}
			});
		},
		cS2RfdedParams: function(obj) {
			var field = 'cS2Rfded_';
			var params = _$.collectParams("input[name^='" + field + "']:text", field, _$.createTips);
			if (!params) {
				return false;
			}
			
			if ($("input[name^='" + field + "tickets[']").length <= 0) {
				_$.createTips("请选择退款成功的乘客");
				return false;
			}
			
			return _$.mergeParams(_$.changeStatusBaseParams(obj), params);
		},
		getOrderDetailHtml: function(orderID) {
			var rtn = '<div class="row">获取失败！</div>';
			$.ajax({
				type: "GET",
				url: "/boss/train/getOrderDetailHtml",
				data: {orderID: orderID},
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
		bindOrderDetailClick: function() {
			$(".c_order_detail").click(function() {
				layer.open({
					title: "订单详情",
					content: _$.getOrderDetailHtml($(this).attr("data-order-id")),
					area: ["80%", "80%"]
				});
			});
		},
		bindSearchClick: function() {
			$(".c_search_time").focus(function() {
				if ($(this).attr("data-flag") == "beginDate") {
					WdatePicker({dateFmt:'yyyy-MM-dd', maxDate: '%y-%M-%d %H:%m'}); 
				} else {
					WdatePicker({dateFmt:'yyyy-MM-dd', minDate: '#F{$(\'.c_search_time\').eq(0).val()}'});
				}
			});
			$(".c_search_all").click(function() {
				$("[data-default-value]").each(function() {
					$(this).val($(this).attr("data-default-value"));
				});
			});
		}
	});
	
	_$.bindChangeStatusClick();
	_$.bindOrderDetailClick();
	_$.bindSearchClick();
});
