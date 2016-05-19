<?php
define('Q_TIME', $_SERVER['REQUEST_TIME']);
define('Q_DATE', date('Y-m-d', Q_TIME));
define('Q_HOST', $_SERVER['HTTP_HOST']);
define('Q_ROOT_PATH', dirname(__FILE__));

define('YII_DEBUG', True);
define('YII_TRACE_LEVEL', 3);

$yiiFile = Q_ROOT_PATH . '/framework/yii.php';
$qmyFile = Q_ROOT_PATH . '/protected/components/Q.php';

require($qmyFile);
require($yiiFile);

Yii::createWebApplication(Q::getConfig())->run();