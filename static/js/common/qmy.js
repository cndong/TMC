(function() {
	var qmy = (function() {
		var qmy = function() {
			return qmy.fn.init();
		}
		qmy.fn = qmy.prototype = {
			init: function() {
				return this;
			}
		}
        qmy.extend = qmy.fn.extend = function() {
            var options, name, src, copy,
            target = arguments[0] || {},
            i = 1,
            length = arguments.length;

            if (length == 1) {
                target = this;
                --i;
            }
            for (; i < length; i++) {
                if ((options = arguments[i]) != null) {
                    for (name in options) {
                        src = target[name];
                        copy = options[name];
                        if (src === copy) {
                            continue;
                        }
                        if (copy !== undefined) {
                            target[name] = copy;
                        }
                    }
                }
            }
            return target;
        }
		return qmy;
	})();
	window.qmy = window._$ = qmy();
})();

_$.extend({
	formats: {
		"INT": /^\d+$/,
		"INTNZ": /^[1-9]\d*$/,
		"FLOAT": /^\d+(\.\d+)?$/,
	    "FLOATZ": /^0(\.0+)?$/,
    	"FLOATNZ": /^(([1-9]|(\d{2,20}))+(\.\d+)?)$/,
    	"TEXTNZ": /^.+$/,
    	"TEXTZ": /^$/,
    	"ALNUM": /^[a-zA-Z0-9]*$/,
    	"ALNUMZ": /^$/,
    	"ALNUMNZ": /^[a-zA-Z0-9]+$/,
    	"DATE_HM": /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/,
    	"DATETIME": /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/,
    	"MOBILE": /^1[3578]\d{9}$/,
    	"CAR_NO": /^[\u4e00-\u9fa5][A-Za-z][A-Za-z0-9]{5}$/,
    	"CARD_NO": /^\d{6}(19|20)\d{2}(0[1-9]|1[012])(0[1-9]|[1-2][0-9]|3[01])\d{3}[\d|x|X]$/,
	},
	getType: function(value) {
		return ((_t = typeof (value)) == "object" ? Object.prototype.toString.call(value).slice(8, -1) : _t).toLowerCase();
	},
	isString: function(value) {
		return _$.getType(value) == "string";
	},
	isNumber: function(value) {
		return _$.getType(value) == "number";
	},
	isArray: function(value) {
		return _$.getType(value) == "array";
	},
	isObject: function(value) {
		return _$.getType(value) == "object";
	},
	isRegExp: function(value) {
		return value instanceof RegExp;
	},
	isDate: function(value) {
		return value instanceof Date;
	},
	testFormat: function(type, value) {
		var re = _$.formats[type];
		return re.test(value);
	},
	collectParams: function(selector, field, func, funcArgs) {
		var params = {};
		var objs = $(selector);
		for (var i = 0; i < objs.length; i++) {
			var obj = objs.eq(i);
			var val = _$.trim(obj.val(), ' ');
			var format = obj.attr("data-format")
			if (format) {
				format = format.split("|");
				var isSucc = true;
				for (var j in format) {
					realFormat = format[j];
					var isNot = realFormat.substr(0, 1) == '!';
					if (isNot) {
						realFormat = realFormat.substring(1);
					}
					if (_$.testFormat(realFormat, val) ^ (!isNot)) {
						isSucc = false;
						break;
					}
				}
				
				if (!isSucc) {
					var err = obj.attr("data-err").split("|");
					if (func) {
						if (func == alert) {
							alert(err);
						} else {
							funcArgs = funcArgs || {};
							funcArgs["_failedName"] = obj.attr("name").substring(field.length);
							funcArgs["_failedFormat"] = format[j];
							funcArgs["_errMsg"] = err[j];
							func(funcArgs);
						}
					}
					return false;
				}
			}
			params[obj.attr("name").substring(field.length)] = val;
		}
		
		return params;
	},
	log: function(params) {
		if (typeof(params) == typeof({})) {
			for (var k in params) {
				console.log(k);
				_$.log(params[k]);
			}
		} else {
			console.log(params);
		}
	},
	mergeParams: function(paramsOne, paramsTwo) {
		for (var k in paramsTwo) {
			if ((_$.isObject(paramsTwo[k]) || _$.isArray(paramsTwo[k])) && !_$.isRegExp(paramsTwo[k]) &&　!_$.isDate(paramsTwo[k])) {
				if (typeof(paramsOne[k]) != typeof({})) {
					paramsOne[k] = {};
				}
				paramsOne[k] = _$.mergeParams(paramsOne[k], paramsTwo[k]);
			} else {
				paramsOne[k] = paramsTwo[k];
			}
		}
		return paramsOne;
	},
	reload: function(type) {
		type = typeof(type) == "undefined" ? true : false;
		location.reload(type);
	},
	go2url: function(url) {
		window.location = url;
	},
	inArray: function(v, arr) {
		for (var x in arr) {
			if (arr[x] == v) {
				return true;
			}
		}
		return false;
	},
	arrayKeys: function(arr) {
		var rtn = new Array();
		for (var index in arr) {
			rtn.push(index);
		}
		return rtn;
	},
	arrayValues: function(arr) {
		var rtn = new Array();
		for (var index in arr) {
			rtn.push(arr[index]);
		}
		return rtn;
	},
	arrayRemoveKeys: function(params, keys) {
		var rtn = {};
		for (var index in params) {
			if (_$.inArray(index, keys)) {
				continue;
			}
			rtn[index] = params[index];
		}
		
		return rtn;
	},
	count: function(arr) {
		var rtn = 0;
		for (var index in arr) {
			rtn++;
		}
		
		return rtn;
	},
	getIndex: function(arr, value) {
		for (var i in arr) {
			if (arr[i] == value) {
				return i;
			}
		}
	},
	evalFunc: function(func) {
		try {
			return eval(func);
		} catch(exception) {
			alert(exception);
		} 
	},
	isset: function(param, k) {
		return typeof(param[k]) != "undefined";
	},
	trim: function(str, trimStr, direction) {
		var pos;
	  
		direction = typeof(direction) != "undefined" ? direction : "both";
		if (direction == "both" || direction == "left") {
			while (str.indexOf(trimStr) == 0) {
				str = str.substr(trimStr.length);
			}
		}

		if (direction == "both" || direction == "right") {
			while ((pos = str.lastIndexOf(trimStr)) >= 0) {
				if (str.substr(pos) == trimStr) {
					str = str.substr(0, pos)
				} else {
					break;
				}
			}
		}
	  
	  return str;
	},
	ltrim: function(str, trimStr) {
		return _$.trim(str, trimStr, "left");
	},
	rtrim: function(str, trimStr) {
		return _$.trim(str, trimStr, "right");
	},
	strpad: function(str, length, fill, direction) {
		fill = typeof(fill) != "undefined" ? fill : " ";
		direction = typeof(direction) != "undefined" ? direction : "both";
		str = String(str);
		fill = String(fill);
		if (str.length >= length) {
			return str;
		}
		
		var leftNum = rightNum = 0;
		var num = length - str.length;
		if (direction == "both") {
			num = num / 2;
			leftNum = Math.ceil(num);
			rightNum = Math.floor(num);
		} else if (direction == "left") {
			leftNum = num;
		} else if (direction == "right") {
			rightNum = num;
		}
		
		for (var i = 0; i < leftNum; i++) {
			str = fill + str;
		}
		
		for (i = 0; i < rightNum; i++) {
			str += fill;
		}
		
		return str;
	},
	ucfirst: function(str) {
		if (str.length <= 0) {
			return str;
		}
		
		return str.substr(0, 1).toUpperCase() + str.substring(1);
	},
	repeatStr: function(str, repeatNum) {
		var rtn = '';
		for (var i = 0; i < repeatNum; i++) {
			rtn += str;
		}
		
		return rtn;
	},
	createTips: function(msg) {
		if (!_$.isString(msg)) {
			msg = msg["_errMsg"];
		}
		
		if ($(".layui-layer-btn0").length <= 0) {
			layer.msg(msg);
		} else {
			layer.tips(msg, ".layui-layer-btn0", {tipsMore: true, tips: 4});
		}
	},
	open: function(type, config, obj) {
		config = config || {};
		config = _$.mergeParams({url: "", prefix: ""}, config);
		var funcName = config["prefix"] + type;
		
		var funcs = {
			"htmlFunc": funcName + "Html",//必须
			"paramsFunc": funcName + "Params",
			"titleFunc": funcName + "Title",
			"showFunc": funcName + "Show",
			"clickFunc": funcName + "Click",
			"overFunc": funcName + "Over",
			"configFunc": funcName + "Config",
			"layerConfigFunc": funcName + "LayerConfig",
		}
		
		for (var funcType in funcs) {
			if (typeof(config[funcType]) != "undefined" && typeof(_$[funcs[funcType]]) == "undefined") {
				_$[funcs[funcType]] = config[funcType];
			}
		}
		
		if (typeof(_$[funcs["configFunc"]]) != "undefined") {
			config = _$.mergeParams(config, _$[funcs["configFunc"]]());
		}
		
		if (typeof(_$[funcs["clickFunc"]]) == "undefined" && !config["url"]) {
			_$.createTips("请求地址配置错误");
			return false;
		}
		
		var defaultClickFunc = function() {
			var field = funcName + "_";
			var params = typeof(_$[funcs["paramsFunc"]]) == "undefined" ? _$.collectParams("*[name^='" + field + "']", field, _$.createTips) : _$[funcs["paramsFunc"]](obj);
			if (params) {
				$.post(config["url"], params, function(data) {
					if (!data.rc) {
						if (typeof(_$[funcs["overFunc"]]) == "undefined") {
							layer.msg("操作成功");
							//_$.reload();
						} else {
							_$[funcs["overFunc"]](obj);
						}
					} else {
						_$.createTips(data.msg);
					}
				}, "json");
			}
		}
		
		var defaultLayerConfig = {
			title: typeof(_$[funcs["titleFunc"]]) == "undefined" ? "提示" : _$[funcs["titleFunc"]],
			area: ["500px"],
			content: _$[funcs["htmlFunc"]](obj),
			btn: ["确定", "取消"],
			yes: typeof(_$[funcs["clickFunc"]]) == "undefined" ? defaultClickFunc : _$[funcs["clickFunc"]]
		}
		
		if (typeof(_$[funcs["layerConfigFunc"]]) != "undefined") {
			defaultLayerConfig = _$.mergeParams(defaultLayerConfig, _$[funcs["layerConfigFunc"]]);
		}
		layer.open(defaultLayerConfig);
		
		if (typeof(_$[funcs["showFunc"]]) != "undefined") {
			_$[funcs["showFunc"]](obj);
		}
	},
});