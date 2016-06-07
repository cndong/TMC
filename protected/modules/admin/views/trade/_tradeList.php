<?php if ($index <= 0) { ?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center" width="15%">类型</th>
            <th class="text-center" width="10%">存入</th>
            <th class="text-center" width="10%">支出</th>
            <th class="text-center" width="10%">余额</th>
            <th class="text-center" width="35%">备注</th>
            <th class="text-center" width="20%">时间</th>
        </tr>
    </thead>
    <tbody>
<?php } ?>
        <tr>
            <td class="text-center"><?php echo CompanyFinanceLog::$types[$data->type]['name']; ?></td>
            <td class="text-center"><?php echo $data->income / 100; ?></td>
            <td class="text-center"><?php echo $data->payout / 100; ?></td>
            <td class="text-center"><?php echo $data->finance / 100; ?></td>
            <td class="text-center"><?php echo $data->getInfoDes(); ?></td>
            <td class="text-center"><?php echo date('Y-m-d H:i:s', $data->ctime); ?></td>
        </tr>
<?php if ($index + 1 == $widget->dataProvider->getItemCount()) { ?>
    </tbody>
</table>
<?php } ?>