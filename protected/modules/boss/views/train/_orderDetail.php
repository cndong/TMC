<?php if ($order->isInvoice) { ?>
<div class="panel panel-default">
    <div class="panel-heading">发票地址</div>
    <div class="panel-body"><?php echo $order->invoiceAddress; ?></div>
</div>
<?php } ?>
<?php 
$routes = $order->getRoutes();
$stations = ProviderT::getStationList();
$routeTypes = $order->isRound ? array('departRoute', 'returnRoute') : array('departRoute');
foreach ($routeTypes as $routeType) {
    $route = $routes[$routeType];
?>
<div class="panel panel-danger">
    <div class="panel-heading text-center"><?php echo "{$stations[$route->departStationCode]['name']}-{$stations[$route->arriveStationCode]['name']}"; ?></div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th>乘客</th>
                <th>票种</th>
                <th>票号</th>
                <th>坐席</th>
                <th>票价</th>
                <th>退款</th>
                <th>保险</th>
                <th>状态</th>
            </tr>
<?php
        foreach ($route->tickets as $ticket) {
            $passenger = UserPassenger::parsePassenger($ticket->passenger);
            echo '<tr>';
            echo "<td>{$passenger['name']}</td>";
            echo '<td>', Dict::$passengerTypes[$passenger['type']]['name'], '</td>';
            echo '<td>', $ticket->ticketNo, '</td>';
            echo '<td>', $ticket->ticketInfo, '</td>';
            echo '<td>', $ticket->ticketPrice / 100, '</td>';
            echo '<td>', empty($ticket->refundPrice) ? 0 : $ticket->refundPrice / 100, '</td>';
            echo '<td>', $ticket->insurePrice / 100, '</td>';
            echo '<td>', TrainStatus::getAdminDes($ticket->status), '</td>';
            echo '</tr>';
?>
<?php
        }
?>
        </table>
    </div>
</div>
<?php 
}
?>
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
                <td><?php echo TrainStatus::getAdminDes($info['status'])?></td>
                <td><?php echo $info['isSucc'] ? '是' : '否'; ?></td>
                <td><?php echo date('Y-m-d H:i:s', $log->ctime); ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
<?php } ?>