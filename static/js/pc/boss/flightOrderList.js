$(function() {
	_$.extend({
		formats: _$.mergeParams(_$.formats, {
			"F_FLIGHT_NO": /^[A-Z0-9]{5,6}$/,
			"F_AIRLINE_CODE": /^[A-Z]{2}$/,
			"F_CRAFT_CODE": /^[A-Z0-9]{3}$/,
			"F_CABIN": /^[A-Z]\d?$/,
			"F_PNR": /^[A-Z0-9]{6}$/,
			"F_TICKET_NO": /^\d{3}-\d{10}$/,
			"F_TERM": /^(T\d)|(--)$/,
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
			area: "70%"
		},
		cS2RsnAgreeTitle: "同意改签",
		cS2RsnAgreeHtml: function(obj) {
			return _$.changeStatusGetHtml(obj);
		},
		cS2RsnAgreeLayerConfig: {
			area: "80%"
		},
		cS2RsnAgreeShow: function(obj) {
			$(".c_select_ticket").change(function() {
				var isChecked = $(this).prop("checked");
				var ticketID = $(this).val();
				var ticketPrice = $(this).attr("data-ticket-price");
				var passenger = $(this).attr("data-passenger");
				var rowID = 'cS2RsnAgreeRow_' + ticketID;
				var diffPriceID = 'cS2RsnAgreeDiffPrice_' + ticketID;
				if (isChecked) {
					$("[name='cS2RsnAgree_flightNo']").val($(this).attr("data-flight-no"));
					$("[name='cS2RsnAgree_departTime']").val($(this).attr("data-depart-time"));
					$("[name='cS2RsnAgree_arriveTime']").val($(this).attr("data-arrive-time"));
					$("[name='cS2RsnAgree_cabinClass']").val($(this).attr("data-cabin-class"));
					$("[name='cS2RsnAgree_isInsured']").prop("checked", $(this).attr("data-is-insured") == "1");
					
					var html = '<div class="row row-form-margin" id="' + rowID + '"><div class="col-sm-2 text-right">' + passenger + '</div><div class="col-sm-10 form-inline">';
					html += '<div class="form-group form-group-sm"><label>票价</label><input type="text" name="cS2RsnAgree_tickets[' + ticketID + '][ticketPrice]" value="' + ticketPrice + '" data-format="FLOATNZ" data-err="' + passenger + '票价错误" data-ticket-price="' + ticketPrice + '" class="k_change_price form-control" size="5" /></div>';
					html += '<div class="form-group form-group-sm hidden"><label>机建</label><input type="text" name="cS2RsnAgree_tickets[' + ticketID + '][airportTax]" value="' + $(this).attr("data-airport-tax") + '" data-format="FLOAT" data-err="' + passenger + '机建费错误"  class="form-control" size="5" /></div>';
					html += '<div class="form-group form-group-sm hidden"><label>燃油</label><input type="text" name="cS2RsnAgree_tickets[' + ticketID + '][oilTax]" value="' + $(this).attr("data-oil-tax") + '" data-format="FLOAT" data-err="' + passenger + '燃油费错误"  class="form-control" size="5" /></div>';
					html += '<div class="form-group form-group-sm"><label>差价</label><input id=' + diffPriceID + ' type="text" value="0" class="form-control" size="5" readonly /></div>';
					html += '<div class="form-group form-group-sm"><label>手续费</label><input type="text" name="cS2RsnAgree_tickets[' + ticketID + '][resignHandlePrice]" data-format="FLOAT" data-err="' + passenger + '手续费错误" class="form-control" size="5" /></div>';
					html += '</div></div>';
					
					$(this).parents(".row").parent().append(html);
					$(".k_change_price").unbind("keyup").keyup(function() {
						$("#" + diffPriceID).val(parseFloat($(this).val()) - parseFloat($(this).attr("data-ticket-price")));
					});
				} else {
					$("#" + rowID).remove();
				}
			});
			$(".c_time").focus(function() {
				WdatePicker({dateFmt:'yyyy-MM-dd HH:mm'});
			});
		},
		cS2RsnAgreeParams: function(obj) {
			var field = 'cS2RsnAgree_';
			var params = _$.collectParams("input[name^='" + field + "']:text,input[name^='" + field + "']:hidden,select[name^='" + field + "'],input:checked", field, _$.createTips);
			if (!params) {
				return false;
			}

			var tickets = $("input[name^='" + field + "ticketIDs[']:checked");
			if (tickets.length <= 0) {
				_$.createTips("请选择要改签的乘客");
				return false;
			}
			
			var ticketTypes = {};
			tickets.each(function() {
				ticketTypes[$(this).attr("data-ticket-type")] = true;
			});
			
			for (var ticketType in ticketTypes) {
				var ticketField = ticketType + "_" + field;
				var ticketParams = _$.collectParams("input[name^='" + ticketField + "']", ticketField, _$.createTips);
				if (!ticketParams) {
					return false;
				}
				
				params = _$.mergeParams(params, ticketParams);
			}
			
			return _$.mergeParams(_$.changeStatusBaseParams(obj), params)
		},
		cS2RsnSuccTitle: "改签成功",
		cS2RsnSuccHtml: function(obj) {
			return _$.changeStatusGetHtml(obj);
		},
		cS2RsnSuccLayerConfig: {
			area: "70%"
		},
		cS2RfdAgreeTitle: "同意退票",
		cS2RfdAgreeHtml: function(obj) {
			return _$.changeStatusGetHtml(obj);
		},
		cS2RfdAgreeShow: function(obj) {
			$(".c_select_ticket").change(function() {
				var ticketID = $(this).attr("data-ticket-id");
				var passengerName = $(this).attr("data-passenger-name");
				var isChecked = $(this).prop("checked");
				if (isChecked) {
					$(this).parent().append('<input type="text" class="form-control input-sm" name="cS2RfdAgree_handlePrice[' + ticketID + ']" data-format="FLOAT" data-err="' + passengerName + '手续费错误" placeholder="手续费" />');
				} else {
					$(this).siblings().remove("input:text");
				}
			});
		},
		cS2RfdAgreeParams: function(obj) {
			var field = 'cS2RfdAgree_';
			var params = _$.collectParams("input[name^='" + field + "']:text", field, _$.createTips);
			if (!params) {
				return false;
			}
			
			if ($("input[name^='" + field + "handlePrice[']").length <= 0) {
				_$.createTips("请选择要退票的乘客");
				return false;
			}
			
			return _$.mergeParams(_$.changeStatusBaseParams(obj), params);
		},
		cS2RfdedTitle: "退款成功",
		cS2RfdedHtml: function(obj) {
			return _$.changeStatusGetHtml(obj);
		},
		cS2RfdedShow: function(obj) {
			$(".c_select_ticket").change(function() {
				var ticketID = $(this).attr("data-ticket-id");
				var passengerName = $(this).attr("data-passenger-name");
				var isChecked = $(this).prop("checked");
				if (isChecked) {
					$(this).parent().append('<input type="text" class="form-control input-sm" name="cS2Rfded_refundPrice[' + ticketID + ']" data-format="FLOAT" data-err="' + passengerName + '实退金额错误" placeholder="实退金额" />');
				} else {
					$(this).siblings().remove("input:text");
				}
			});
		},
		cS2RfdedParams: function(obj) {
			var field = 'cS2Rfded_';
			var params = _$.collectParams("input[name^='" + field + "']:text", field, _$.createTips);
			if (!params) {
				return false;
			}
			
			if ($("input[name^='" + field + "refundPrice[']").length <= 0) {
				_$.createTips("请选择退款成功的乘客");
				return false;
			}
			
			return _$.mergeParams(_$.changeStatusBaseParams(obj), params);
		},
	});
	
	_$.bindChangeStatusClick();
});