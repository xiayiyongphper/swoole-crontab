<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'id' => 'app-crontab',
    'basePath' => dirname(__DIR__),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mainDb' => __env_get_mysql_db_config('lelai_slim_common'),
        'productDb' => __env_get_mysql_db_config('lelai_booking_product_a'),
        'customerDb' => __env_get_mysql_db_config('lelai_slim_customer'),
        'proxyDb' => __env_get_mysql_db_config('swoole_proxy'),
        'coreReadOnlyDb' => __env_get_mysql_readonly_db_config('lelai_slim_core'),
        'coreDb' => __env_get_mysql_db_config('lelai_slim_core'),
        'merchantDb' => __env_get_mysql_db_config('lelai_slim_merchant'),
        'cmsDb' => __env_get_mysql_db_config('lelai_slim_backend'),
        'commonDb' => __env_get_mysql_db_config('lelai_slim_common'),
        'resultDb' => __env_get_mysql_slave_db_config('lelai_result'),
        'redisCache' => __env_get_redis_config(),
        'elasticSearch' => __env_get_elasticsearch_config(),
        'es_logger' => [
            'class' => '\framework\components\log\RedisLogger',
            'redis' => __env_get_elk_redis_config(),
            'logKey' => 'logstash-lelai-dinghuo'
        ],
        'mq' => __env_get_mq_config(),
        'consumer_mq' => __env_get_mq_config(),
        'routeRedisCache' => __env_get_route_redis_config(),
        'session' => __env_get_session_config(),
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true, //转换目录访问
            'showScriptName' => false, //去除index
            'rules' => [
                '<controller:[\w+(-)?]+>/<action:[\w+(-)?]+>' => '<controller>/<action>',
            ],
        ],
    ],
];
