<?php
interface SMSInterface {
    public function send($params, $type = '');
}