<?php if ($order->isInvoice) { ?>
<div class="panel panel-default">
    <div class="panel-heading">发票地址</div>
    <div class="panel-body"><?php echo $order->invoiceAddress; ?></div>
</div>
<?php } ?>
<?php 
$routes = $order->getRoutes();
$cities = ProviderF::getCNCityList();
$airports = ProviderF::getCNAirportList();
$classifyTickets = FlightCNOrder::classifyTickets($order->tickets, 'segmentID');
$routeTypes = $order->isRound ? array('departRoute', 'returnRoute') : array('departRoute');
foreach ($routeTypes as $routeType) {
    $routeTypeName = $routeType == 'departRoute' ? '去' : '返';
    foreach ($routes[$routeType]['segments'] as $segment) {
        if (empty($classifyTickets[$segment->id])) {
            continue;
        }
?>
<div class="panel panel-danger">
    <div class="panel-heading text-center"><?php echo "{$cities[$segment['departCityCode']]['cityName']}({$airports[$segment['departAirportCode']]['airportName']})-{$cities[$segment['arriveCityCode']]['cityName']}({$airports[$segment['arriveAirportCode']]['airportName']})-{$routeTypeName}"; ?></div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th>乘客姓名</th>
                <th>票种</th>
                <th>PNR</th>
                <th>票号</th>
                <th>票价</th>
                <th>机建</th>
                <th>燃油</th>
                <th>改签手续费</th>
                <th>退票手续费</th>
                <th>退款金额</th>
                <th>保险</th>
                <th>状态</th>
            </tr>
<?php
        foreach ($segment->tickets as $ticket) {
            $passenger = FlightCNOrder::parsePassenger($ticket->passenger);
            echo '<tr>';
            echo "<td>{$passenger['name']}</td>";
            echo '<td>', DictFlight::$ticketTypes[$passenger['type']]['name'], '</td>';
            echo '<td>', empty($ticket->smallPNR) ? '--' : $ticket->smallPNR, '</td>';
            echo '<td>', empty($ticket->ticketNo) ? '--' : $ticket->ticketNo, '</td>';
            echo '<td>', $ticket->ticketPrice / 100, '/', $ticket->realTicketPrice / 100, '</td>';
            echo '<td>', $ticket->airportTax / 100, '</td>';
            echo '<td>', $ticket->oilTax / 100, '</td>';
            echo '<td>', $ticket->resignHandlePrice / 100, '/', $ticket->realResignHandlePrice / 100, '</td>';
            echo '<td>', $ticket->refundHandlePrice / 100, '/', $ticket->realRefundHandlePrice / 100, '</td>';
            echo '<td>', $ticket->refundPrice / 100, '</td>';
            echo '<td>', $ticket->insurePrice / 100, '</td>';
            echo '<td>', FlightStatus::getAdminDes($ticket->status), '</td>';
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
}
?>
<?php if (!empty($finances)) { ?>
<div class="panel panel-danger">
    <div class="panel-heading text-center">交易记录</div>
    <div class="panel-body">
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th>类型</th>
                <th>存入</th>
                <th>支出</th>
                <th>余额</th>
                <th>备注</th>
                <th>时间</th>
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
<div class="panel panel-danger">
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
                <td><?php echo FlightStatus::getAdminDes($info['status'])?></td>
                <td><?php echo $info['isSucc'] ? '是' : '否'; ?></td>
                <td><?php echo date('Y-m-d H:i:s', $log->ctime); ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
<?php } ?>