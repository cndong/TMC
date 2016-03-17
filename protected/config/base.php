<?php
return array(
    'basePath' => Q_ROOT_PATH . '/protected',
    'name' => '【去买票】火车票,火车票查询,火车票订购,机票查询,特价机票,打折飞机票-去买票Qumaipiao.com',
    'preload' => array('log'),
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.dicts.*',
        'application.extensions.*'
    ),
    'modules' => array(
        'api',
        'admin',
        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => '123456',
            'ipFilters' => array('127.0.0.1', '::1', '10.10.11.100'),
        ),
    ),
    'components' => array(
        'request' => array(
            'csrfTokenName' => 'QToken',
        ),
        'urlManager' => array(
            'urlFormat' => 'path',
            'showScriptName' => False,
            'rules'=>array(
                'flight/wx/booking/<from:.+>-<to:.+>/<date:.*>'=>'flight/wx/booking',
                'flight/wx/buy/<from:.+>-<to:.+>/<date:.*>/<flightNo:.+>/<subCabin:.+>'=>'flight/wx/buy',
                'flight/wx/orderList'=>'flight/wx/orderList',
            )
        ),
        'errorHandler' => array(
            // use 'site/error' action to display errors
            //'errorAction' => 'flight/site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                    'categories' => 'dberror.*',
                    'logFile' => 'dberror.log',
                )
            ),
        ),
    ),

    'params' => array(
        'adminEmail' => 'wangbendong@meiti.com',
        'keyword' => '火车票预订、火车票查, 询机票, 机票预订',
        'des' => '去买票! qumaipiao.com, 致力于做最好的网上订票服务平台, 提供火车票预订、火车票查询、机票预订、机票查询等服务。',
    ),
);