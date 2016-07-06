<?php
return array(
    'basePath' => Q_ROOT_PATH . '/protected',
    'name' => '【TMC系统】',
    'preload' => array('log'),
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.datas.*',
        'application.extensions.*',
        'application.extensions.sms.*',
        'application.extensions.push.*',
        'application.extensions.phpMailer.*',
    ),
    'defaultController' => 'boss/index',
    'modules' => array(
        'api',
        'boss',
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
            'rules' => array(
                'User/<action:.+>/*' => 'api/User/<action>',
                'Flight/<action:.+>/*' => 'api/Flight/<action>',
                'Train/<action:.+>/*' => 'api/Train/<action>',
                'Bus/<action:.+>/*' => 'api/Bus/<action>',
                'Hotel/<action:.+>/*' => 'api/Hotel/<action>',
                'System/<action:.+>/*' => 'api/System/<action>',
                'Provider/<action:.+>/*' => 'api/Provider/<action>',
                //'Provider/<action:.+>/*' => 'api/Provider/<action>',
            )
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
                ),
                array(
                        'class' => 'CFileLogRoute',
                        'levels' => 'error, warning',
                        'categories' => 'Provider.CNBOOKING.*',
                        'logFile' => 'Provider.CNBOOKING.log',
                ),
                /*
                array(
                    'class' => 'CWebLogRoute',
                )
                */
            ),
        ),
        'cache'=> array(
            'class' => 'system.caching.CMemCache',
            'useMemcached' => extension_loaded('Memcached'),
        ),
        'db' => array(
            'class' => 'system.db.CDbConnection',
            'schemaCachingDuration' => 432000,
            'emulatePrepare' => True,
            'enableProfiling' => False,
            'charset' => 'utf8',
            'tablePrefix' => 'tmc_',
        ),
    )
);