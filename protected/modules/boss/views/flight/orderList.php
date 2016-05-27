<?php $this->registerJqueryJs(); ?>
<?php $this->registerLayerJs(); ?>
<?php $this->registerQmyJs(); ?>
<!--div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">订单搜索</div>
            <div class="panel-body">
                <form class="form-inline">
                    <div class="form-group form-group-sm">
                        <label>订单ID</label>
                        <input type="text" class="form-control" name="orderID" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div-->
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">订单列表</div>
            <?php $this->createListView($dataProvider); ?>
        </div>
    </div>
</div>
<?php $this->registerActionJs(); ?>