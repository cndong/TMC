<?php if (!empty($finances)) { ?>
<div class="panel panel-success">
    <div class="panel-heading text-center">交易记录</div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th width="8%">类型</th>
                <th width="8%">存入</th>
                <th width="8%">支出</th>
                <th width="8%">余额</th>
                <th width="48%">备注</th>
                <th width="20%">时间</th>
            </tr>
            <?php foreach ($finances as $finance) { ?>
            <tr>
                <td><?php echo CompanyFinanceLog::$types[$finance->type]['name']; ?></td>
                <td><?php echo $finance->income / 100; ?></td>
                <td><?php echo $finance->payout / 100; ?></td>
                <td><?php echo $finance->finance / 100; ?></td>
                <td><?php echo $finance->getInfoDes(); ?></td>
                <td><?php echo date('Y-m-d H:i:s', $finance->ctime); ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
<?php } ?>
<?php if (!empty($logs)) { ?>
<div class="panel panel-warning">
    <div class="panel-heading text-center">操作记录</div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th width="33.33%">状态</th>
                <th width="33.33%">成功</th>
                <th width="33.33%">时间</th>
            </tr>
            <?php
                foreach ($logs as $log) {
                    $info = json_decode($log->info, True);
            ?>
            <tr>
                <td><?php echo HotelStatus::getAdminDes($info['status'])?></td>
                <td><?php echo $info['isSucc'] ? '是' : '否'; ?></td>
                <td><?php echo date('Y-m-d H:i:s', $log->ctime); ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
<?php } ?>