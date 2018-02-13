<?php
namespace service\processes;

use common\components\push\CustomerEnterpriseJiGuang;
use common\components\push\CustomerJiGuang;
use common\components\push\MerchantJiGuang;
use framework\components\ToolsAbstract;
use framework\core\ProcessInterface;
use framework\core\SWServer;
use service\tasks\Ex;

/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 16-6-2
 * Time: 上午11:12
 */

/**
 * Class MsgPushProcess 推送消息
 * @package service\processes
 */
class MsgPushProcess implements ProcessInterface
{
    const MESSAGE_PUSH_QUEUE = 'message_push_queue';

    /**
     * @inheritdoc
     */
    public function run(SWServer $SWServer, \swoole_process $process)
    {
        $redis = ToolsAbstract::getRedis();
        while (true) {
            try {
                if ($redis->lLen(self::MESSAGE_PUSH_QUEUE) == 0) {
                    sleep(1);
                    continue;
                }
                /** @var  array $queue */
                $message = $redis->lPop(self::MESSAGE_PUSH_QUEUE);
                $message = unserialize($message);
                ToolsAbstract::log('message=' . print_r($message, 1), 'MsgPushProcess.log');
                if (is_array($message)) {
                    $result = self::send($message);
                    ToolsAbstract::log('push result=' . print_r($result, 1), 'MsgPushProcess.log');
                    if (!$result) {
                        //不成功时需要记录下来
                    }
                }
            } catch (\Exception $e) {
                ToolsAbstract::logException($e);
            }
        }
    }

    /**
     * Function: send
     * Author: Jason Y. Wang
     * 推送消息
     * @param $message
     * @return bool
     */
    public static function send($message)
    {
        $mobilesys = 'android';
        $queue = new CustomerJiGuang();
        switch ($message['platform']) {
            case 1:
                if ($message['channel'] >= 100000 && $message['channel'] < 200000) {
                    $mobilesys = 'ios';
                    $queue = new CustomerJiGuang();
                } elseif ($message['channel'] >= 200000 && $message['channel'] < 300000) {
                    $mobilesys = 'ios';
                    $queue = new CustomerEnterpriseJiGuang();
                } else {
                    $mobilesys = 'android';
                    $queue = new CustomerJiGuang();
                }
                break;
            case 2:
                if ($message['channel'] >= 100000 && $message['channel'] < 300000) {
                    $mobilesys = 'ios';
                    $queue = new MerchantJiGuang();
                } else {
                    $mobilesys = 'android';
                    $queue = new MerchantJiGuang();
                }
                break;
            default:
                break;
        }

        try {
            $params = isset($message['params']) ? $message['params'] : null;
            if (!$params) {
                throw Ex::getException(Ex::EX_PUSH_BASE);
            }

            /* 兼容后台发过来的消息类型，如果是string类型，再次反序列化 */
            if (is_string($params)) {
                if (($arr = unserialize($params)) && is_array($arr)) {
                    $params = $arr;
                }
            }

            if (empty($message['token'])) {
                ToolsAbstract::log($message, 'MsgPushProcess-empty-token.log');
                return false;
            }

            //推送参数
            $data = [
                'user_id' => $message['token'],
                'title' => $params['title'],
                'content' => $params['content'],
                'scheme' => $params['scheme'],
                'mobilesys' => $mobilesys,
                'sendno' => rand(1, 100000)
            ];
            //推送
            $result = $queue->push($data);
            return $result;
        } catch (\Exception $e) {
            ToolsAbstract::logException($e);
        } catch (\Error $e) {
            ToolsAbstract::logError($e);
        }
    }
}