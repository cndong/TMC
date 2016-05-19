<?php
return array(
    'components' => array(
        'cache'=> array(
            'servers' => array(
                array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 50)
            ),
        ),
        'db' => array(
            'connectionString' => 'mysql:host=qumaiyain.mysql.rds.aliyuncs.com;dbname=tmc;port=3303;',
            'enableProfiling' => False,
            'username' => 'root',
            'password' => '',
        ),
    )
);