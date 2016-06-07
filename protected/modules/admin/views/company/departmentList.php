<?php $this->registerJqueryJs(); ?>
<?php $this->registerLayerJs(); ?>
<?php $this->registerQmyJs(); ?>
<div class="row">
    <div class="col-lg-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <form class="form-inline" action="<?php echo $this->createUrl('departmentList'); ?>">
                    <span>部门列表</span>
                    <input name="search" type="text" class="form-control input-sm" placeholder="编号/名称..." value="<?php echo $params['search']; ?>" />
                    <button type="submit" class="btn btn-default input-sm">查询</button>
                    <button class="c_create btn btn-danger btn-sm pull-right" data-company-id="<?php echo $this->admin->companyID; ?>" data-company-name="<?php echo $this->admin->company->name; ?>" data-create-type="createDepartment" type="button">+添加部门</button>
                </form>
            </div>
            <?php $this->createListView($dataProvider); ?>
        </div>
    </div>
</div>
<?php $this->registerControllerJs(); ?>