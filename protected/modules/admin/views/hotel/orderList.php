<?php $this->registerJqueryJs(); ?>
<?php $this->registerQmyJs(); ?>
<?php $this->registerDatePickerJs(); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <form class="form-inline" name="searchForm" action="<?php echo $this->createUrl('orderList'); ?>">
                    <label>订单号</label>
                    <input name="orderID" type="text" class="form-control input-sm" size="6" value="<?php echo empty($params['orderID']) ? '' : $params['orderID']; ?>" data-default-value="" />
                    <label>起始时间</label>
                    <input name="beginDate" type="text" data-flag="beginDate" class="c_search_time form-control input-sm" size="10" value="<?php echo $params['beginDate']; ?>" data-default-value="" />
                    <label>结束时间</label>
                    <input name="endDate" type="text" data-flag="endDate" class="c_search_time form-control input-sm" size="10" value="<?php echo $params['endDate']; ?>" data-default-value="" />
                    <button type="submit" class="btn btn-sm btn-info">搜索</button>
                    <button type="submit" class="c_search_all btn btn-sm btn-primary">全部</button>
                </form>
            </div>
            <?php $this->createListView($dataProvider); ?>
        </div>
    </div>
</div>
<?php $this->registerActionJs(); ?>