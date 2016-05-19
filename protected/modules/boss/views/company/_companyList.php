<?php if ($index <= 0) { ?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center" width="15%">企业编号</th>
            <th class="text-center" width="35%">企业名称</th>
            <th class="text-center" width="30%">创建时间</th>
            <th class="text-center" width="20%">操作</th>
        </tr>
    </thead>
    <tbody>
<?php } ?>
        <tr>
            <td class="text-center"><?php echo $data->id; ?></td>
            <td class="text-center"><?php echo $data->name; ?></td>
            <td class="text-center"><?php echo date('Y-m-d H:i:s', $data->ctime); ?></td>
            <td class="text-center">
                <button data-company-id="<?php echo $data->id; ?>" data-company-name="<?php echo $data->name; ?>" class="c_department btn btn-primary btn-sm">部门</button>
                <button data-company-id="<?php echo $data->id; ?>" data-company-name="<?php echo $data->name; ?>" class="c_user btn btn-primary btn-sm">员工</button>
            </td>
        </tr>
<?php if ($index + 1 == $widget->dataProvider->getItemCount()) { ?>
    </tbody>
</table>
<?php } ?>