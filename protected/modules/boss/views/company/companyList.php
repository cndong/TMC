<?php $this->registerJqueryJs(); ?>
<?php $this->registerLayerJs(); ?>
<?php $this->registerQmyJs(); ?>
<div class="row">
    <div class="col-lg-7">
        <div class="panel panel-default">
            <div class="panel-heading">
                <form class="form-inline">
                    <span>企业列表</span>
                    <input name="search" type="text" class="form-control input-sm" placeholder="编号/名称..." value="<?php echo $params['search']; ?>" />
                    <button type="submit" class="btn btn-default input-sm">查询</button>
                    <button class="c_create btn btn-danger btn-sm pull-right" data-create-type="company" type="button">+添加企业</button>
                </form>
            </div>
            <?php $this->createListView($dataProvider); ?>
        </div>
    </div>
</div>
<?php $this->registerControllerJs(); ?>