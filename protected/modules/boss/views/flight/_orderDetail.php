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