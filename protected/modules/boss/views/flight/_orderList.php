<?php
    if ($index <= 0) {
        /*
	    $_batchNos = F::arrayGetField($widget->dataProvider->getData(), 'batchNo');
	    $_orders = FlightCNOrder::getByBatchNos($_batchNos);
	    */
?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center" width="15%">订单信息</th>
            <th class="text-center" width="5%">往返</th>
            <th class="text-center" width="20%">航程信息</th>
            <th class="text-center" width="10%">联系人</th>
            <th class="text-center" width="5%">价格</th>
            <th class="text-center" width="10%">用户信息</th>
            <th class="text-center" width="10%">状态</th>
            <th class="text-center" width="10%">客服</th>
            <th class="text-center" width="10%">操作</th>
        </tr>
    </thead>
    <tbody>
<?php } ?>
        <tr>
            <td class="text-center"><?php echo $data->id, '<br />', date('Y-m-d H:i:s', $data->ctime), '<br />', Merchant::$merchants[$data->merchantID]['name']; ?></td>
            <td class="text-center"><?php echo $data->isRound ? '是' : '否'; ?></td>
            <td class="text-center">
            <?php 
                foreach ($data->segments as $segment) {
                    echo '航段:', $segment->departAirportCode, '-', $segment->arriveAirportCode, '<br />';
                    echo '时间:', date('Y-m-d H:i', $segment->departTime), '<br />';
                }
            ?>
            </td>
            <td class="text-center"><?php echo $data->contacter->name, '<br />', $data->contacter->mobile; ?></td>
            <td class="text-center"><?php echo $data->orderPrice / 100; ?></td>
            <td class="text-center"><?php echo "{$data->user->name}({$data->userID})<br />{$data->department->name}({$data->departmentID})<br />{$data->company->name}($data->companyID)"; ?></td>
            <td class="text-center"><?php echo FlightStatus::getAdminDes($data->status); ?></td>
            <td class="text-center"><?php echo empty($data->operaterID) ? '无' : $data->operater->nickname; ?></td>
            <td class="text-center">
                No
            </td>
        </tr>
<?php if ($index + 1 == $widget->dataProvider->getItemCount()) { ?>
    </tbody>
</table>
<?php } ?>