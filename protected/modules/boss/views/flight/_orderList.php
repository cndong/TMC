<?php
    if ($index <= 0) {
?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center" width="13%">订单信息</th>
            <th class="text-center" width="5%">往返</th>
            <th class="text-center" width="10%">联系人</th>
            <th class="text-center" width="5%">价格</th>
            <th class="text-center" width="12%">用户信息</th>
            <th class="text-center" width="15%">状态</th>
            <th class="text-center" width="10%">客服</th>
            <th class="text-center" width="20%">操作</th>
            <th class="text-center" width="10%">退款成功</th>
        </tr>
    </thead>
    <tbody>
<?php } ?>
        <tr>
            <td rowspan="3" class="text-center"><?php echo $data->id, '<br />', date('m-d H:i:s', $data->ctime), '<br />', Merchant::$merchants[$data->merchantID]['name']; ?></td>
            <td class="text-center"><?php echo $data->isRound ? '是' : '否'; ?></td>
            <td class="text-center"><?php echo $data->contactName, '<br />', $data->contactMobile; ?></td>
            <td class="text-center"><?php echo $data->orderPrice / 100; ?></td>
            <td class="text-center"><?php echo "{$data->user->name}<br />{$data->department->name}<br />{$data->company->name}"; ?></td>
            <td class="text-center"><?php echo FlightStatus::getAdminDes($data->status); ?></td>
            <td class="text-center"><?php echo empty($data->operaterID) ? '无' : $data->operater->nickname; ?></td>
            <td rowspan="3" class="text-center">
                <div class="btn-group btn-group-sm">
                <?php
                    $counter = 0;
                    foreach (FlightStatus::getAdminHdStatus($data->status) as $toStatus) {
                        if (($checkFunc = FlightStatus::getCheckFunc($toStatus)) && !$data->$checkFunc()) {
                            continue;
                        }
                        $counter++;
                        $toStatusConfig = FlightStatus::$flightStatus[$toStatus];
                        $btnColor = empty($toStatusConfig['btnColor']) ? 'info' : $toStatusConfig['btnColor'];
                        $btn = empty($toStatusConfig['btn']) ? '接单' : $toStatusConfig['btn'];
                        echo '<button class="c_change_status btn btn-' . $btnColor . '" data-is-handle="1" data-order-id="' . $data->id . '" data-status="' . $toStatus . '" data-status-str="' . $toStatusConfig['str'] . '">' . $btn . '</button>';
                    }
                    
                    foreach (FlightStatus::getAdminOpStatus($data->status) as $toStatus) {
                        if ((($checkFunc = FlightStatus::getCheckFunc($toStatus)) && !$data->$checkFunc()) || $data->operaterID != $this->admin->id) {
                            continue;
                        }
                        $counter++;
                        $toStatusConfig = FlightStatus::$flightStatus[$toStatus];
                        $btnColor = empty($toStatusConfig['btnColor']) ? 'info' : $toStatusConfig['btnColor'];
                        echo '<button class="c_change_status btn btn-' . $btnColor . '" data-is-handle="0" data-order-id="' . $data->id . '" data-status="' . $toStatus . '" data-status-str="' . $toStatusConfig['str'] . '">' . $toStatusConfig['btn'] . '</button>';
                    }
                    
                    echo $counter <= 0 ? '无' : '';
                ?>
                </div>
            </td>
            <td rowspan="3" class="text-center">
                <?php echo $data->isCanRefunded() ? '<button class="c_change_status btn btn-danger btn-sm" data-is-handle="0" data-order-id="' . $data->id . '" data-status="' . FlightStatus::RFDED . '" data-status-str="' . FlightStatus::$flightStatus[FlightStatus::RFDED]['str'] . '">退款成功</button>' : '无'; ?>
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <?php 
                    $cities = DataAirport::getCNCities();
                    $classifyPassengers = FlightCNOrder::classifyPassengers(FlightCNOrder::parsePassengers($data->passengers));
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
            <td colspan="6">
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