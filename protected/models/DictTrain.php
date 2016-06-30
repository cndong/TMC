<?php
class DictTrain {
    const MAX_PASSENGER_NUM = 5;
    
    const INSURE_ID = 1;
    const INSURE_PRICE = 2000;
    
    const JOURNEY_TYPE_DC = 1;
    const JOURNEY_TYPE_WF = 2;
    
    const SEAT_NO = 1;
    const SEAT_HARD = 2;
    const SEAT_SOFT = 3;
    const SEAT_SECOND = 4;
    const SEAT_FIRST = 5;
    const SEAT_SECOND_SOFT = 6;
    const SEAT_FIRST_SOFT = 7;
    const SEAT_SPECIAL = 8;
    const SEAT_BUSINESS = 9;
    const SEAT_HARD_SLEEP_UP = 10;
    const SEAT_HARD_SLEEP_MID = 11;
    const SEAT_HARD_SLEEP_DOWN = 12;
    const SEAT_SOFT_SLEEP_UP = 13;
    const SEAT_SOFT_SLEEP_DOWN = 14;
    const SEAT_ADVANCE_SOFT_SLEEP = 15;
    const SEAT_D_SLEEP_UP = 16;
    const SEAT_D_SLEEP_DOWN = 17;
    const SEAT_ADVANCE_D_SLEEP_UP = 18;
    const SEAT_ADVANCE_D_SLEEP_DOWN = 19;
    const SEAT_OTHER = 20;
    //动卧SRRB、高级动卧YYRW
    //二等软座 T7788次列车等
    //其他(一人软包、包厢硬卧) T32次列车等
    //yp_info
    //1011253126403010000010112000003019400000, 10个字数一组, 第一位是座位类别, 之后5位是价格****.*, 之后是余票数量****, 如果余票数量 > 3000, 则表示无座且数量为 N - 3000
    public static $seatTypes = array(
        self::SEAT_NO => array('name' => '无座'),
        self::SEAT_HARD => array('name' => '硬座'),
        self::SEAT_SOFT => array('name' => '软座'),
        self::SEAT_SECOND => array('name' => '二等座'),
        self::SEAT_FIRST => array('name' => '一等座'),
        self::SEAT_SECOND_SOFT => array('name' => '二等软座'),
        self::SEAT_FIRST_SOFT => array('name' => '一等软座'),
        self::SEAT_SPECIAL => array('name' => '特等座'),
        self::SEAT_BUSINESS => array('name' => '商务座'),
        self::SEAT_HARD_SLEEP_UP => array('name' => '硬卧上铺', 'cname' => '硬卧'),
        self::SEAT_HARD_SLEEP_MID => array('name' => '硬卧中铺', 'cname' => '硬卧'),
        self::SEAT_HARD_SLEEP_DOWN => array('name' => '硬卧下铺', 'cname' => '硬卧'),
        self::SEAT_SOFT_SLEEP_UP => array('name' => '软卧下铺', 'cname' => '软卧'),
        self::SEAT_SOFT_SLEEP_DOWN => array('name' => '软卧上铺', 'cname' => '软卧'),
        self::SEAT_ADVANCE_SOFT_SLEEP => array('name' => '高级软卧'),
        self::SEAT_D_SLEEP_UP => array('name' => '动卧上铺', 'cname' => '动卧'),
        self::SEAT_D_SLEEP_DOWN => array('name' => '动卧下铺', 'cname' => '动卧'),
        self::SEAT_ADVANCE_D_SLEEP_UP => array('name' => '高级动卧上铺', 'cname' => '高级动卧'),
        self::SEAT_ADVANCE_D_SLEEP_DOWN => array('name' => '高级动卧下铺', 'cname' => '高级动卧'),
        self::SEAT_OTHER => array('name' => '其他'),
    );
}