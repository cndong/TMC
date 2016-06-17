<?php $this->registerJqueryJs(); ?>
<?php $this->registerLayerJs(); ?>
<?php $this->registerQmyJs(); ?>
<?php $this->registerDatePickerJs(); ?>
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
            <div class="panel-heading">
                <form class="form-inline" name="searchForm">
                    <span>订单列表</span>
                    <select name="searchType" class="form-control input-sm" data-default-value="0">
                        <option value="0">--搜索类别--
                        <?php 
                        $searchValue = '';
                        foreach ($this->getRenderParams('searchTypes') as $searchType => $searchName) {
                            $selected = '';
                            if (!empty($params[$searchType])) {
                                $selected = ' selected';
                                $searchValue = $params[$searchType];
                            }
                            echo "<option value='{$searchType}'{$selected}>{$searchName}";
                        }
                        ?>
                    </select>
                    <input name="searchValue" type="text" class="form-control input-sm" size="6" value="<?php echo $searchValue; ?>" data-default-value="" />
                    <label>起始时间</label>
                    <input name="beginDate" type="text" data-flag="beginDate" class="c_search_time form-control input-sm" size="10" value="<?php echo $params['beginDate']; ?>" data-default-value="" />
                    <label>结束时间</label>
                    <input name="endDate" type="text" data-flag="endDate" class="c_search_time form-control input-sm" size="10" value="<?php echo $params['endDate']; ?>" data-default-value="" />
                    <select name="status" class="form-control input-sm" data-default-value="0">
                        <option value="0">--搜索状态--
                        <?php
                            foreach (FlightStatus::$flightStatus as $status => $_) {
                                $selected = !empty($params['status']) && in_array($status, $params['status']) ? ' selected' : '';
                                echo "<option value='{$status}'{$selected}>", FlightStatus::getAdminDes($status);
                            }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-info">搜索</button>
                    <button type="submit" class="c_search_all btn btn-sm btn-primary">全部</button>
                </form>
            </div>
            <?php $this->createListView($dataProvider, array('viewData' => array('params' => $params))); ?>
        </div>
    </div>
</div>
<?php $this->registerActionJs(); ?>