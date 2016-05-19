<?php
return array(
    'components' => array(
        'cache'=> array(
            'servers' => array(
                array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 50)
            ),
        ),
        'db' => array(
            'connectionString' => 'mysql:host=localhost;dbname=tmc',
            'enableProfiling' => True,
            'username' => 'root',
            'password' => '',
        ),
    )
);