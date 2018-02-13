<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/9/4
 * Time: 18:09
 */

namespace service\tasks\test;


use common\helpers\MessageHelper;
use framework\components\ToolsAbstract;
use service\tasks\TaskService;
use framework\message\Message;

class testRpc extends TaskService
{
    public function run($data)
    {
        $client = new \swoole_client(SWOOLE_SOCK_UNIX_STREAM);
        $client->set([
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,       // 第N个字节是包长度的值
            'package_body_offset' => 4,       // 第几个字节开始计算长度
            'package_max_length' => 2000000,  // 协议最大长度
            'socket_buffer_size' => 1024 * 1024 * 2, // 2M缓存区
        ]);

        $client->connect(\Yii::$app->params['ip_port']['host'], ENV_SERVER_PORT);
        $message = MessageHelper::pack('taskTest.test1', ['from' => 'rpc']);
        $client->send($message);

        ToolsAbstract::log('testRpc:send,data=' . $message, 'xxxx.log');
        $recvMsg = $client->recv();

        $message = new Message();
        $message->unpackResponse($recvMsg);

        ToolsAbstract::log('testRpc:recv,data=' . print_r($message->getPackageBody(), 1), 'xxxx.log');
        $client->close();
    }
}