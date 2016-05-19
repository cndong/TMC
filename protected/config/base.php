<?php
return array(
    'basePath' => Q_ROOT_PATH . '/protected',
    'name' => '【TMC系统】',
    'preload' => array('log'),
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.extensions.*',
        'application.datas.*',
    ),
    'defaultController' => 'boss/index',
    'modules' => array(
        'boss',
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
            'showScriptName' => False
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
                /*
                array(
                    'class' => 'CWebLogRoute'
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