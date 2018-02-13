<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/3
 * Time: 16:42
 */

namespace common\helpers;

use common\models\customer\DeviceToken;
use framework\components\ToolsAbstract;
use service\processes\MsgPushProcess;

/**
 * 推送助手类
 * @package common\helpers
 */
class PushHelper
{
    /**
     * @param array $data
     * @param mixed $content
     * @param string|null $scheme
     * @param string|null $title
     * @return bool
     */
    public static function pushMessage($data, $content, $scheme = null, $title = null)
    {
        if (empty($data['customer_id'])) {
            return false;
        }

        // 数据
        $customer_id = $data['customer_id'];
        $redis = ToolsAbstract::getRedis();

        ToolsAbstract::log($data, 'PushHelper.txt');

        /** @var  DeviceToken $queue */
        $queue = DeviceToken::findOne(['customer_id' => $customer_id]);
        if ($queue && $queue->entity_id && $queue->token) {
            $params = [
                'title' => $title ? $title : '乐来订货网',
                'content' => $content,
            ];

            // 默认不传scheme会打开app
            if ($scheme !== null) {
                $params['scheme'] = $scheme;
            } elseif (isset($data['order_id'])) {
                $params['scheme'] = 'lelaishop://order/info?oid=' . $data['order_id'];
            } else {
                $params['scheme'] = 'lelaishop://';
            }

            $message = [
                'system' => $queue->system,
                'token' => $queue->token,
                'platform' => 1,
                'channel' => $queue->channel,
                'value_id' => $customer_id,
                'typequeue' => $queue->typequeue,
                'params' => serialize($params),
            ];

            Tools::log($message, 'PushHelper.txt');

            $redis->lPush(MsgPushProcess::MESSAGE_PUSH_QUEUE, serialize($message));
            return true;
        }
        return false;
    }
}