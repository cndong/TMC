<?php
    if ($index <= 0) {
?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center" width="15%">订单信息</th>
            <th class="text-center" width="5%">因公</th>
            <th class="text-center" width="5%">保险</th>
            <th class="text-center" width="5%">往返</th>
            <th class="text-center" width="8%">联系人</th>
            <th class="text-center" width="5%">价格</th>
            <th class="text-center" width="15%">用户信息</th>
            <th class="text-center" width="12%">状态</th>
            <th class="text-center" width="10%">客服</th>
            <th class="text-center" width="10%">操作</th>
            <th class="text-center" width="10%">退款成功</th>
        </tr>
    </thead>
    <tbody>
<?php } ?>
        <tr>
            <td rowspan="3" class="text-center"><?php echo "<a href='javascript:;' class='c_order_detail' data-order-id='{$data->id}'>{$data->id}</a>", '<br />', date('m-d H:i:s', $data->ctime), '<br />', Merchant::$merchants[$data->merchantID]['name']; ?><br /></td>
            <td class="text-center"><?php echo $data->isPrivate ? '否' : '是'; ?></td>
            <td class="text-center"><?php echo $data->isInsured ? '有' : '无'; ?></td>
            <td class="text-center"><?php echo $data->isRound ? '是' : '否'; ?></td>
            <td class="text-center"><?php echo $data->contactName, '<br />', $data->contactMobile; ?></td>
            <td class="text-center"><?php echo $data->orderPrice / 100; ?></td>
            <td class="text-center"><?php echo "{$data->user->name}<br />{$data->department->name}<br />{$data->company->name}"; ?></td>
            <td class="text-center"><?php echo TrainStatus::getAdminDes($data->status); ?></td>
            <td class="text-center"><?php echo empty($data->operaterID) ? '无' : $data->operater->nickname; ?></td>
            <td rowspan="3" class="text-center">
                <p><button class="c_order_detail btn btn-sm btn-primary" data-order-id="<?php echo $data->id; ?>">查看</button></p>
                <?php
                    $counter = 0;
                    foreach (TrainStatus::getSysHdStatus($data->status) as $toStatus) {
                        if (($checkFunc = TrainStatus::getCheckFunc($toStatus)) && !$data->$checkFunc()) {
                            continue;
                        }
                        $counter++;
                        $toStatusConfig = TrainStatus::$trainStatus[$toStatus];
                        $btnColor = empty($toStatusConfig['btnColor']) ? 'info' : $toStatusConfig['btnColor'];
                        $btn = empty($toStatusConfig['btn']) ? '接单' : $toStatusConfig['btn'];
                        echo '<p><button class="c_change_status btn btn-sm btn-' . $btnColor . '" data-is-handle="1" data-order-id="' . $data->id . '" data-status="' . $toStatus . '" data-status-str="' . $toStatusConfig['str'] . '">' . $btn . '</button><p>';
                    }
                    
                    foreach (TrainStatus::getSysOpStatus($data->status) as $toStatus) {
                        if ((($checkFunc = TrainStatus::getCheckFunc($toStatus)) && !$data->$checkFunc()) || $data->operaterID != $this->admin->id) {
                            continue;
                        }
                        $counter++;
                        $toStatusConfig = TrainStatus::$trainStatus[$toStatus];
                        $btnColor = empty($toStatusConfig['btnColor']) ? 'info' : $toStatusConfig['btnColor'];
                        echo '<p><button class="c_change_status btn btn-sm btn-' . $btnColor . '" data-is-handle="0" data-order-id="' . $data->id . '" data-status="' . $toStatus . '" data-status-str="' . $toStatusConfig['str'] . '">' . $toStatusConfig['btn'] . '</button></p>';
                    }
                ?>
            </td>
            <td rowspan="3" class="text-center">
                <?php //echo $data->isCanRefunded() ? '<button class="c_change_status btn btn-sm btn-danger btn-sm" data-is-handle="0" data-order-id="' . $data->id . '" data-status="' . TrainStatus::RFDED . '" data-status-str="' . TrainStatus::$trainStatus[TrainStatus::RFDED]['str'] . '">退款成功</button>' : '无'; ?>
            </td>
        </tr>
        <tr>
            <td colspan="8">
                <?php 
                    $stations = ProviderT::getStationList();
                    $classifyPassengers = UserPassenger::classifyPassengers(UserPassenger::parsePassengers($data->passengers), Dict::BUSINESS_TRAIN);
                    foreach ($data->routes as $route) {
                        echo '<span class="pull-left text-danger">';
                        echo "【行程】{$stations[$route->departStationCode]['name']}-{$stations[$route->arriveStationCode]['name']}";
                        echo "【车次】{$route->trainNo}, ", DictTrain::$seatTypes[$route->seatType]['name'];
                        echo '【价格】', $route['ticketPrice'];
                        echo '【时间】', date('Y-m-d H:i', $route->departTime), '</span>';
                    }
                ?>
            </span>
            </td>
        </tr>
        <tr>
            <td colspan="8">
                <?php 
                    foreach ($classifyPassengers as $passengerType => $passengers) {
                        if (!empty($passengers)) {
                            $ticketTypeName = Dict::$passengerTypes[$passengerType]['name'];
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