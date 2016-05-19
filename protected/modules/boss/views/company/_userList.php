<?php if ($index <= 0) { ?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center" width="10%">员工编号</th>
            <th class="text-center" width="10%">员工姓名</th>
            <th class="text-center" width="10%">员工手机</th>
            <th class="text-center" width="20%">所属部门</th>
            <th class="text-center" width="20%">所属企业</th>
            <th class="text-center" width="20%">创建时间</th>
            <th class="text-center" width="10%">操作</th>
        </tr>
    </thead>
    <tbody>
<?php } ?>
        <tr>
            <td class="text-center"><?php echo $data->id; ?></td>
            <td class="text-center"><?php echo $data->name; ?></td>
            <td class="text-center"><?php echo $data->mobile; ?></td>
            <td class="text-center"><?php echo $data->department->name; ?></td>
            <td class="text-center"><?php echo $data->company->name; ?></td>
            <td class="text-center"><?php echo date('Y-m-d H:i:s', $data->ctime); ?></td>
            <td class="text-center">无</td>
        </tr>
<?php if ($index + 1 == $widget->dataProvider->getItemCount()) { ?>
    </tbody>
</table>
<?php } ?>