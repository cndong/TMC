<?php if ($index <= 0) { ?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center" width="10%">部门编号</th>
            <th class="text-center" width="25%">部门名称</th>
            <th class="text-center" width="25%">所属企业</th>
            <th class="text-center" width="20%">创建时间</th>
            <th class="text-center" width="20%">操作</th>
        </tr>
    </thead>
    <tbody>
<?php } ?>
        <tr>
            <td class="text-center"><?php echo $data->id; ?></td>
            <td class="text-center"><?php echo $data->name; ?></td>
            <td class="text-center"><?php echo $data->company->name; ?></td>
            <td class="text-center"><?php echo date('Y-m-d H:i:s', $data->ctime); ?></td>
            <td class="text-center">
                <button data-company-id="<?php echo $data->companyID; ?>" data-company-name="<?php echo $data->company->name; ?>" data-department-id="<?php echo $data->id; ?>" data-department-name="<?php echo $data->name; ?>" class="c_user btn btn-primary btn-sm">员工</button>
            </td>
        </tr>
<?php if ($index + 1 == $widget->dataProvider->getItemCount()) { ?>
    </tbody>
</table>
<?php } ?>