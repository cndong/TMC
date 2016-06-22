<?php
    if ($index <= 0) {
?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center" width="15%">订单信息</th>
            <th class="text-center" width="8%">往返</th>
            <th class="text-center" width="8%">价格</th>
            <th class="text-center" width="13%">联系人</th>
            <th class="text-center" width="10%">用户信息</th>
            <th class="text-center" width="20%">部门</th>
            <th class="text-center" width="8%">审批人</th>
            <th class="text-center" width="20%">状态</th>
        </tr>
    </thead>
    <tbody>
<?php } ?>
        <tr>
            <td rowspan="3" class="text-center"><?php echo $data->id, '<br />', date('m-d H:i:s', $data->ctime), '<br />', Merchant::$merchants[$data->merchantID]['name']; ?></td>
            <td class="text-center"><?php echo $data->isRound ? '是' : '否'; ?></td>
            <td class="text-center"><?php echo $data->orderPrice / 100; ?></td>
            <td class="text-center"><?php echo $data->contactName, '<br />', $data->contactMobile; ?></td>
            <td class="text-center"><?php echo $data->user->name; ?></td>
            <td class="text-center"><?php echo $data->department->name; ?></td>
            <td class="text-center"><?php echo !empty($data->reviewerID) ? $data->reviewer->name : '无'; ?></td>
            <td class="text-center"><?php echo FlightStatus::getUserDes($data->status); ?></td>
        </tr>
        <tr>
            <td colspan="7">
                <?php 
                    $cities = DataAirport::getCNCities();
                    $classifyPassengers = FlightCNOrder::classifyPassengers(UserPassenger::parsePassengers($data->passengers));
                    foreach ($data->segments as $segment) {
                        echo '<span class="pull-left text-danger">';
                        echo "【航段】{$cities[$segment->departCityCode]['cityName']}({$segment->departCityCode})-{$cities[$segment->arriveCityCode]['cityName']}({$segment->arriveCityCode})";
                        echo "【航班】{$segment->flightNo}, {$segment->cabin}";
                        foreach ($classifyPassengers as $ticketType => $passengers) {
                            if (!empty($passengers)) {
                                $ticketTypeStr = DictFlight::$ticketTypes[$ticketType]['str'];
                                echo '【', DictFlight::$ticketTypes[$ticketType]['name'], '价格】', $segment[$ticketTypeStr . 'Price'] / 100, '-', $segment[$ticketTypeStr . 'AirportTax'] / 100, '-', $segment[$ticketTypeStr . 'OilTax'] / 100;
                            }
                        }
                        echo '【时间】', date('Y-m-d H:i', $segment->departTime), '</span>';
                    }
                ?>
            </span>
            </td>
        </tr>
        <tr>
            <td colspan="7">
                <?php 
                    foreach ($classifyPassengers as $ticketType => $passengers) {
                        if (!empty($passengers)) {
                            $ticketTypeName = DictFlight::$ticketTypes[$ticketType]['name'];
                            foreach ($passengers as $passenger) {
                                $cardType = Dict::$cardTypes[$passenger['cardType']]['name'];
                                $sex = Dict::$sexTypes[$passenger['sex']]['name'];
                                echo "<span class='pull-left text-warning'>【{$ticketTypeName}-{$passenger['name']}-{$cardType}-{$passenger['cardNo']}-{$passenger['birthday']}-{$sex}】</span>";
                            }
                        }
                    }
                ?>
                </span>
            </td>
        </tr>
<?php if ($index + 1 == $widget->dataProvider->getItemCount()) { ?>
    </tbody>
</table>
<?php } ?>