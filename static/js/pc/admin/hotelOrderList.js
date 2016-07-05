$(function() {
	_$.extend({
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

	_$.bindSearchClick();
});