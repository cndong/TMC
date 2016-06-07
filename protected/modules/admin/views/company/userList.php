<?php $this->registerJqueryJs(); ?>
<?php $this->registerLayerJs(); ?>
<?php $this->registerQmyJs(); ?>
<div class="row">
    <div class="col-lg-10">
        <div class="panel panel-default">
            <div class="panel-heading">
                <form class="form-inline" action="<?php echo $this->createUrl('userList'); ?>">
                    <span>员工列表</span>
                    <input name="departmentID" type="text" class="form-control input-sm" value="<?php echo $params['departmentID']; ?>" placeholder="部门编号" />
                    <input name="search" type="text" class="form-control input-sm" placeholder="员工编号/姓名/手机..." value="<?php echo $params['search']; ?>" />
                    <button type="submit" class="btn btn-default input-sm">查询</button>
                    <button class="c_create btn btn-danger btn-sm pull-right" data-company-id="<?php echo $this->admin->companyID; ?>" data-company-name="<?php echo $this->admin->company->name; ?>" data-create-type="createUser" type="button">+添加员工</button>
                </form>
            </div>
            <?php $this->createListView($dataProvider); ?>
        </div>
    </div>
</div>
<?php $this->registerControllerJs(); ?>