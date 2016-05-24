<?php
class FlightStatus {
    const WAIT_CHECK = 1;
    const CHECK_FAIL = 2;
    const CHECK_SUCC = 3;
    const WAIT_PAY = 4; //个人票使用
    const PAYED = 5; //个人票使用
    const BOOKING = 6;
    const BOOK_FAIL = 7;
    const BOOK_SUCC = 8;
    const APPLY_RFD = 9;
    const APPLY_RFD_FAIL = 10;
    const APPLY_RFD_SUCC = 11;
}