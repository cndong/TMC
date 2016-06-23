<?php $this->registerJqueryJs(); ?>
<?php $this->registerQmyJs(); ?>
<?php $this->registerDatePickerJs(); ?>
<div class="row">
    <div class="col-lg-10">
        <div class="panel panel-default">
            <div class="panel-heading">
                <form class="form-inline" action="<?php echo $this->createUrl('tradeList'); ?>">
                    <span>交易列表</span>
                    <!--
                    <select name="searchType" class="form-control input-sm" data-default-value="0">
                        <option value="0">--搜索类别--
                        <?php 
                        $searchValue = '';
                        foreach ($searchTypes as $searchType => $searchName) {
                            $selected = '';
                            if (!empty($params[$searchType])) {
                                $selected = ' selected';
                                $searchValue = $params[$searchType];
                            }
                            echo "<option value='{$searchType}'{$selected}>{$searchName}";
                        }
                        ?>
                    </select>
                    <input type="text" name="searchValue" class="form-control input-sm" size="10" value="<?php echo $searchValue; ?>" data-default-value="" />
                    -->
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
<?php $this->registerControllerJs(); ?>