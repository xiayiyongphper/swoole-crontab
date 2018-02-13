<?php
namespace service\tasks\customer;

use common\helpers\PushHelper;
use common\models\LeCustomers;
use service\tasks\TaskService;

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/14
 * Time: 14:37
 */

/**
 * 零钱推送
 * @package service\tasks\customer
 */
class pushBalanceMessage extends TaskService
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        $customers = LeCustomers::find()->where(['>=', 'balance', 10])->all();
        /** @var LeCustomers $customer */
        foreach ($customers as $customer) {
            $data = [
                'customer_id' => $customer->entity_id,
                'order_id' => ''  //无用
            ];

            $content = '您有' . $customer->balance . '元零钱未使用，下单可直抵现金哦';
            PushHelper::pushMessage($data, $content, 'lelaishop://');
        }
    }
}