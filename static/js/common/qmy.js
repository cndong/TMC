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
		"PNR": /^[A-Z0-9]{6}$/,
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
    	"M_ORDER_ID": /^[a-zA-Z0-9]{10,32}$/,
    	"OID": /^\d{15}$/,
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
			if (typeof(paramsTwo[k]) == typeof({})) {
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
	}
});