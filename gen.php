<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');
define('ENV_MYSQL_REMOTE_DB_HOST', '121.201.109.95');
define('ENV_MYSQL_REMOTE_DB_USER', 'ilelaidev');
define('ENV_MYSQL_REMOTE_DB_PWD', 'ifenilelai@1028');
//define('ENV_MYSQL_REMOTE_DB_USER', 'lelai_show');
//define('ENV_MYSQL_REMOTE_DB_PWD', 'LVwYSWLBhyyzpdjA');

require(__DIR__ . '/common/config/env.php');
require(__DIR__ . '/vendor/lelaisoft/framework/autoload.php');
require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/common/config/bootstrap.php');
require(__DIR__ . '/generator/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/common/config/main.php'),
    require(__DIR__ . '/common/config/main-local.php'),
    require(__DIR__ . '/generator/config/main.php'),
    require(__DIR__ . '/generator/config/main-local.php')
);

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);