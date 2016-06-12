<?php $this->registerJqueryJs(); ?>
<?php $this->registerLayerJs(); ?>
<?php $this->registerQmyJs(); ?>
<div class="btn-group btn-group-sm">
    <button class="c_department btn btn-danger" data-company-id="<?php echo $company->id; ?>" data-company-name="<?php echo $company->name; ?>">部门管理</button>
    <button class="c_user btn btn-warning" data-company-id="<?php echo $company->id; ?>" data-company-name="<?php echo $company->name; ?>">用户管理</button>
</div>
<div class="row row-form-margin">
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading">企业信息</div>
            <div class="panel-body">
                <p class="text-success">企业名称:<b><?php echo $company->name; ?></b></p>
                <p class="text-warning">现有部门:<b><?php echo $departmentNum; ?></b></p>
                <p class="text-info">现有员工:<b><?php echo $userNum; ?></b></p>
                <p class="text-danger">账户余额:<b>¥<?php echo $company->finance / 100; ?></b></p>
            </div>
            <div class="panel-footer">创建时间:<?php echo date('Y-m-d H:i:s', $company->ctime); ?></div>
        </div>
    </div>
</div>
<?php $this->registerControllerJs(); ?>