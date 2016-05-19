<?php
return array(
    'components' => array(
        'cache'=> array(
            'servers' => array(
                array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 50)
            ),
        ),
        'db' => array(
            'connectionString' => 'mysql:host=127.0.0.1;dbname=tmc',
            'enableProfiling' => True,
            'username' => 'root',
            'password' => 'Qumaiya520',
        ),
    )
);