<?php
    if ($index <= 0) {
?>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center" width="8%">订单信息</th>
            <th class="text-center" width="18%">酒店信息</th>
            <th class="text-center" width="5%">价格</th>
            <th class="text-center" width="8%">联系人</th>
            <th class="text-center" width="12%">预定事宜</th>
            <th class="text-center" width="8%">审批人</th>
            <th class="text-center" width="8%">状态</th>
            <th class="text-center" width="8%">备注</th>
        </tr>
    </thead>
    <tbody>
<?php } ?>
        <tr>
            <td class="text-center"><?php echo $data->id, '<br />', date('m-d H:i:s', $data->ctime), '<br />', Merchant::$merchants[$data->merchantID]['name']; ?></td>
            <td class="text-center"><?php echo $data->hotelName.'<br />'.$data->checkIn.'~'.$data->checkOut.'<br />'.$data->roomName.' '.Hotel::$bedLimitArray[$data->bedLimit].' '.Hotel::$breakfastArray[$data->breakfast].'('.$data->roomCount.'间)'.'<br />'.$data->guestName; ?></td>
            <td class="text-center"><?php echo $data->orderPrice; ?></td>
            <td class="text-center"><?php echo $data->bookName.'<br />'.$data->bookPhone; ?></td>
            <td class="text-center"><?php echo $data->reason.'<br />'.$data->user->name.'<br />'.$data->department->name;; ?></td>
            <td class="text-center"><?php echo !empty($data->reviewerID) ? $data->reviewer->name : '无'; ?></td>
            <td class="text-center"><?php echo HotelStatus::getUserDes($data->status); ?></td>
            <td class="text-center"><?php echo $data->specialRemark; ?></td>
        </tr>
<?php if ($index + 1 == $widget->dataProvider->getItemCount()) { ?>
    </tbody>
</table>
<?php } ?>