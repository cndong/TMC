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
<div class="panel panel-default">
    <div class="panel-heading text-danger"><?php echo "{$cities[$segment['departCityCode']]}({$airports[$segment['departAirportCode']]})-{$cities[$segment['arriveCityCode']]}({$airports[$segment['arriveAirportCode']]})-{$routeTypeName}"; ?></div>
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
                <th>保险</th>
                <th>状态</th>
            </tr>
<?php
        foreach ($segment->tickets as $ticket) {
            '';
?>
        </table>
    </div>
</div>
<?php
        }
    }
}
?>