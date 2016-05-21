<?php
interface SMSInterface {
    public function getNum($content);
    public function send($params, $type);
}