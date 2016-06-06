$(function() {
	_$.extend({
		getDepartmentsOptions: function(companyID) {
			var html = '';
			$.ajax({
				url: "/admin/company/ajaxDepartmentList",
				dataType: "json",
				async: false,
				success: function(data) {
					if (!data.rc) {
						for (var i in data.data.departmentList) {
							var department = data.data.departmentList[i];
							html += '<option value="' + department["id"] + '">' + department["name"];
						}
					}
				}
			});
			return html;
		},
		createDepartmentTitle: "添加部门",
		createDepartmentHtml: function(obj) {
			var companyID = obj.attr("data-company-id");
			var companyName = obj.attr("data-company-name");
			var html = '<div class="row"><label class="col-sm-3 control-label text-right">所属企业:</label><div class="col-sm-6">';
			if (companyID) {
				html += companyName;
				html += '<input type="hidden" name="create_department_companyID" value="' + companyID + '" />';
			} else {
				html += '<select name="create_department_companyID" class="form-control input-sm" data-format="INTNZ" data-err="请选择所属企业">';
				html += '<option value="0">----请选择----';
				html += _$.getCompaniesOptions();
				html += '</select>';
			}
			html += '</div></div>';
			html += '<div class="row row-form-margin"><div class="form-group form-group-sm"><label class="col-sm-3 control-label text-right">部门名称:</label><div class="col-sm-6"><input type="text" class="form-control" name="create_department_name" data-format="!TEXTZ" data-err="部门名称不能为空!" /></div></div></div>';
			
			return html;
		},
		createDepartmentParams: function() {
			var field = "create_department_";
			return _$.collectParams("input[name^='" + field + "'],select[name^='" + field + "']", field, _$.createTips);
		},
		createUserTitle: "添加员工",
		createUserHtml: function(obj) {
			var companyID = obj.attr("data-company-id");
			var companyName = obj.attr("data-company-name");
			var departmentID = obj.attr("data-department-id");
			var departmentName = obj.attr("data-department-name");
			departmentID = departmentID || "";
			departmentName = departmentName || "";
			
			var html = '<div class="row"><label class="col-sm-3 control-label text-right">所属企业:</label><div class="col-sm-6">';
			if (companyID) {
				html += companyName;
				html += '<input type="hidden" name="create_user_companyID" value="' + companyID + '" />';
			} else {
				html += '<select name="create_user_companyID" class="c_show_departments form-control input-sm" data-format="INTNZ" data-err="请选择所属企业">';
				html += '<option value="0">----请选择----';
				html += _$.getCompaniesOptions();
				html += '</select>';
			}
			html += '</div></div>';
			html += '<div class="row row-form-margin"><label class="col-sm-3 control-label text-right">所属部门:</label><div class="col-sm-6">';
			if (departmentID) {
				html += departmentName;
				html += '<input type="hidden" name="create_user_departmentID" value="' + departmentID + '" />';
			} else {
				html += '<select name="create_user_departmentID" class="form-control input-sm" data-format="INTNZ" data-err="请选择所属部门">';
				html += '<option value="0">----请选择----';
				if (companyID) {
					html += _$.getDepartmentsOptions(companyID);
				}
				html += '</select>';
			}
			html += '</div></div>';
			html += '<div class="row row-form-margin"><div class="form-group form-group-sm"><label class="col-sm-3 control-label text-right">员工姓名:</label><div class="col-sm-6"><input type="text" class="form-control" name="create_user_name" data-format="!TEXTZ|TEXTNZ" data-err="员工姓名不能为空!|员工姓名错误!" /></div></div></div>';
			html += '<div class="row row-form-margin"><div class="form-group form-group-sm"><label class="col-sm-3 control-label text-right">员工手机:</label><div class="col-sm-6"><input type="text" class="form-control" name="create_user_mobile" data-format="!TEXTZ|MOBILE" data-err="员工手机不能为空!|员工手机错误!" /></div></div></div>';
			html += '<div class="row row-form-margin"><div class="form-group form-group-sm"><label class="col-sm-3 control-label text-right">员工密码:</label><div class="col-sm-6"><input type="text" class="form-control" name="create_user_password" data-format="!TEXTZ" data-err="员工密码不能为空!" /></div></div></div>';
			html += '<div class="row row-form-margin"><div class="form-group form-group-sm"><label class="col-sm-3 control-label text-right">是否审核:</label><div class="col-sm-6"><label class="radio-inline"><input type="radio" name="create_user_isReviewer" value="1" />审核人</label><label class="radio-inline"><input type="radio" name="create_user_isReviewer" value="0" checked />非审核人</label></div></div></div>';
			
			return html;
		},
		createUserParams: function() {
			var field = "create_user_";
			return _$.collectParams("input[name^='" + field + "']:text,select[name^='" + field + "'],input:checked", field, _$.createTips);
		},
		createUserShow: function(obj) {
			var companySelect = $("select[name='create_user_companyID']");
			companySelect.change(function() {
				var companyID = $(this).val()
				if (parseInt(companyID) > 0) {
					var departmentSelect = $("select[name='create_user_departmentID']");
					var html = '<option value="0">----请选择----';
					html += _$.getDepartmentsOptions(companyID);
					departmentSelect.html(html);
				}
			});
		},
		createBindClick: function() {
			$(".c_create").unbind("click").click(function() {
				var createType = $(this).attr("data-create-type");
				_$.open(createType, {url: "/admin/company/" + createType}, $(this));
			});
		}
	});
	
	_$.createBindClick();
	
	$(".c_department").click(function() {
		var companyID = $(this).attr("data-company-id");
		var companyName = $(this).attr("data-company-name");
		
		var html = '<div class="row"><div class="col-sm-6"><button data-company-id="' + companyID + '" data-company-name="' + companyName + '" data-create-type="createDepartment" class="c_create btn btn-danger btn-sm pull-right">添加部门</button></div><div class="col-sm-6"><a href="/admin/company/departmentList/" class="btn btn-primary btn-sm pull-left">部门列表</a></div></div>';
		layer.open({content: html, title: false, btn: 0})
		
		_$.createBindClick();
	});
	$(".c_user").click(function() {
		var companyID = $(this).attr("data-company-id");
		var companyName = $(this).attr("data-company-name");
		var departmentID = $(this).attr("data-department-id");
		var departmentName = $(this).attr("data-department-name");
		departmentID = departmentID || "";
		departmentName = departmentName || "";
		
		var html = '<div class="row"><div class="col-sm-6"><button data-company-id="' + companyID + '" data-company-name="' + companyName + '" data-department-id="' + departmentID + '" data-department-name="' + departmentName + '" data-create-type="createUser" class="c_create btn btn-danger btn-sm pull-right">添加员工</button></div><div class="col-sm-6"><a href="/admin/company/userList/departmentID/' + departmentID + '" class="btn btn-primary btn-sm pull-left">员工列表</a></div></div>';
		layer.open({content: html, title: false, btn: 0});
		
		_$.createBindClick();
	});
});