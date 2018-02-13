<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 17-10-13
 * Time: 上午11:06
 */

namespace common\helpers;


use common\models\core\SalesFlatOrder;
use framework\components\ToolsAbstract;

class SaasHelper
{
    public static function notifySaas($data)
    {
        $orderId = $data['value']['order']['entity_id'];
        //订单生成延迟，aftersave在commit之前，导致消息在订单生成前发送，后面解决，这是临时解决方案
        for ($i = 0; $i < 20; $i++) {
            $order = SalesFlatOrder::find()->where(['entity_id' => $orderId])->one();
            if ($order) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, ENV_SYNC_SAAS_URL);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, ['order_id' => $orderId]);
                $result = curl_exec($ch);
                curl_close($ch);
                ToolsAbstract::log('orderId:' . $orderId, 'order_sync.log');
                ToolsAbstract::log($result, 'order_sync.log');
                return true;
            }
            ToolsAbstract::log(microtime(true) . ':' . $i, 'order_sync.log');
            usleep(250000);  //sleep(0.2)不能这样使用
        }

        ToolsAbstract::log($data, 'order_sync.log');
        return false;

    }

}