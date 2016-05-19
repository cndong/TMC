(function(_$) {
	_$.extend({
		dateClone: function(d) {
			return new Date(d.valueOf());
		},
		dateAddZero: function(num) {
			num = parseInt(num);
			if (num < 10) {
				num = "0" + num;
			}
			return num;
		},
		dateIsDate: function(d) {
			return d.constructor == Date;
		},
		dateGetDate: function(d) {
			d = d || new Date();
			return _$.dateIsDate(d) ? d : new Date(d);
		},
		dateGetDayZH: function(day) {
			var config = ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"];
			return config[day];
		},
		dateGetYMD: function(date) {
			date = _$.dateGetDate(date);
			return date.getFullYear() + "-" + _$.dateAddZero(date.getMonth() + 1) + "-" + _$.dateAddZero(date.getDate());
		},
		dateGetYMDZH: function(str) {
			var arr = str.split("-");
			var rtn = arr[0] + "年" + arr[1] + "月";
			if (arr.length > 2) {
				rtn += arr[2] + "日";
			}
			return rtn;
		},
		//把Date对象转换为普通对象
		dateGetDict: function(date) {
			date = _$.dateGetDate(date);
			return {
				year: date.getFullYear(),
				month: date.getMonth(),
				date: date.getDate(),
				day: date.getDay(),
				ymd: _$.dateGetYMD(date)
			};
		},
		//根据Date对象获取前后几天的字符串
		dateGetOffset: function(date, num, rtnType) {
			date = _$.dateGetDate(date);
			num = num || 0;
			rtnType = rtnType || "str";
			
			date.setDate(date.getDate() + num);
			if (rtnType == "str") {
				return _$.dateGetYMD(date);
			} else if (rtnType == "obj") {
				return date;
			}
			
			return _$.dateGetDict(date);
		},
		//获取本月最后一天
		dateGetLastDate: function(date) {
			date = _$.dateGetDate(date);
			date = _$.dateClone(date);
			var month = date.getMonth();
			while (date.getMonth() == month) {
				date.setDate(date.getDate() + 1);
			}
			
			date.setDate(date.getDate() - 1);
			return date;
		},
		//获取要展示的日期数据
		dateGetSelectorData: function(date, dayCanLeft, dayCanRight) {
			var rtn = {
				monthLeft: "",
				monthRight: "",
				year: "",
				month: "",
				days: []
			}
			
			var dateObj = new Date(date);
			var dateDict = _$.dateGetDict(dateObj);
			
			rtn.year = dateDict.year;
			rtn.month = _$.dateAddZero(dateDict.month + 1);
			
			var now = new Date();
			var lastDateNum = _$.dateGetLastDate(dateObj).getDate();
			
			now.setDate(now.getDate() - dayCanLeft);
			var minDate = _$.dateGetDict(now);
			
			now.setDate(now.getDate() + dayCanLeft + dayCanRight);
			var maxDate = _$.dateGetDict(now);
			
			if (dateDict.year + _$.dateAddZero(dateDict.month) > minDate.year + _$.dateAddZero(minDate.month)) {
				var month = rtn.month;
				rtn.monthLeft = month > 1 ? (rtn.year + "-" + _$.dateAddZero(month - 1)) : ((rtn.year - 1) + "-" + 12);
			}
			if (dateDict.year + _$.dateAddZero(dateDict.month) < maxDate.year + _$.dateAddZero(maxDate.month)) {
				var month = parseInt(rtn.month);
				rtn.monthRight = month < 12 ? (rtn.year + "-" + _$.dateAddZero(month + 1)) : ((rtn.year + 1) + "-" + "01");
			}
			
			for (var i = 1; i <= lastDateNum; i++) {
				dateObj.setDate(i);
				var ymd = _$.dateGetYMD(dateObj);
				var tmp = {date: i, day: dateObj.getDay(), readonly: true};
				if (ymd >= minDate.ymd && ymd <= maxDate.ymd) {
					tmp.readonly = false;
				}
				rtn.days[i - 1] = tmp;
			}
			return rtn;
		},
		//获取日期数据的当前月份的前后缀日期
		dateCreateZeroArr: function(day, isPrefix) {
			if (isPrefix) {
				var from = 0;
				var to = day;
			} else {
				var from = day;
				var to = 6;
			}
			var rtn = [];
			for (var i = from; i <= to; i++) {
				rtn.push({date: 0});
			}
			return rtn;
		},
		//需要提前导入jQuery DatePicker插件
		dateBindPicker: function(selector) {
			$(selector).datepicker(
				$.extend({
					showMonthAfterYear:false
				},
				$.datepicker.regional['zh_cn'],
				{
					'showAnim':'fold',
					'dateFormat':'yy-mm-dd',
					'class':'trainCalendar',
					'style':'font:16px;',
					'buttonImage':'/images/daigou/s_time.gif',
					'buttonImageOnly':true,
					'readonly':'readonly',
					'monthNames':['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
					'showMonthAfterYear':true
				})
			);
		}
	});
})(_$);