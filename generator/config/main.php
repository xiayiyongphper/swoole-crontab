<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php'),
    require(__DIR__ . '/../../service/config/server-config.php')
);

return [
    'id' => 'app-generator',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'generator\controllers',
    'components' => [
        'customerDb' => [
            'class' => ENV_DB_CONNECTION_CLASS,
            'dsn' => sprintf('mysql:host=%s;dbname=%s;port=%s', ENV_MYSQL_REMOTE_DB_HOST, 'lelai_slim_customer', 3306),
            'username' => ENV_MYSQL_REMOTE_DB_USER,
            'password' => ENV_MYSQL_REMOTE_DB_PWD,
            'charset' => 'utf8',
        ],
        'merchantDb' => [
            'class' => ENV_DB_CONNECTION_CLASS,
            'dsn' => sprintf('mysql:host=%s;dbname=%s;port=%s', ENV_MYSQL_REMOTE_DB_HOST, 'lelai_slim_merchant', 3306),
            'username' => ENV_MYSQL_REMOTE_DB_USER,
            'password' => ENV_MYSQL_REMOTE_DB_PWD,
            'charset' => 'utf8',
        ],
        'productsDb' => [
            'class' => ENV_DB_CONNECTION_CLASS,
            'dsn' => sprintf('mysql:host=%s;dbname=%s;port=%s', ENV_MYSQL_REMOTE_DB_HOST, 'lelai_booking_product_a', 3306),
            'username' => ENV_MYSQL_REMOTE_DB_USER,
            'password' => ENV_MYSQL_REMOTE_DB_PWD,
            'charset' => 'utf8',
        ],
//        'coreDb' => [
//            'class' => ENV_DB_CONNECTION_CLASS,
//            'dsn' => sprintf('mysql:host=%s;dbname=%s;port=%s', ENV_MYSQL_REMOTE_DB_HOST, 'lelai_slim_core', 3306),
//            'username' => ENV_MYSQL_REMOTE_DB_USER,
//            'password' => ENV_MYSQL_REMOTE_DB_PWD,
//            'charset' => 'utf8',
//        ],
        'coreDb' => [
            'class' => ENV_DB_CONNECTION_CLASS,
            'dsn' => sprintf('mysql:host=%s;dbname=%s;port=%s', ENV_MYSQL_DB_HOST, 'test', 3306),
            'username' => ENV_MYSQL_DB_USER,
            'password' => ENV_MYSQL_DB_PWD,
            'charset' => 'utf8',
        ],
        'testDb' => [
            'class' => ENV_DB_CONNECTION_CLASS,
            'dsn' => sprintf('mysql:host=%s;dbname=%s;port=%s', ENV_MYSQL_DB_HOST, 'test', 3306),
            'username' => ENV_MYSQL_DB_USER,
            'password' => ENV_MYSQL_DB_PWD,
            'charset' => 'utf8',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
