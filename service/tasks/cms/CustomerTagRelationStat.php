<?php

namespace service\tasks\cms;


use common\helpers\Tag;
use common\helpers\Tools;
use common\models\CustomerTagRelation;
use common\models\LeCustomers;
use service\tasks\TaskService;

/**
 * Author Jason Y.Wang
 * Class CustomerTagRelationStat
 * @package service\resources\crontab\v1
 *
 * 供业务员筛选超市时使用
 * 每天跑一次，在CustomersAllTagStat之后跑
 * 业务员V1.6版本加入
 */
class CustomerTagRelationStat extends TaskService
{


    const CLASSIFY_ACTIVE = 7;  //活跃用户
    const CLASSIFY_FOCUS = 8;  //重点关注用户
    const CLASSIFY_SILENT = 9;  //沉默用户
    const CLASSIFY_MONTH_ORDERED = 11;  //本月已下单
    const CLASSIFY_MONTH_NONE_ORDERED = 12; //本月未下单

    public function run($data = null)
    {

        Tools::log('start', 'CustomerTagRelationStat.log');
        $timeStamp = Tools::getDate()->timestamp();
        $this_month = date('Y-m-01', $timeStamp);

        //活跃用户统计  活跃上升用户和活跃下降用户
        $customer_rise_key = Tag::getCurrentTagKey(Tag::ACTIVE_RISE_CUSTOMERS_TAG);
        $customer_decline_key = Tag::getCurrentTagKey(Tag::ACTIVE_DECLINE_CUSTOMERS_TAG);
        $customer_rise = Tag::getMountedCustomersPro($customer_rise_key);
        $customer_decline = Tag::getMountedCustomersPro($customer_decline_key);
        $customer_active = array_merge($customer_rise, $customer_decline);
        $this->saveData(self::CLASSIFY_ACTIVE, $customer_active);

        //重点关注用户统计  轻度流失用户，未下单新用户，已下单新用户，重新激活用户
        $active_light_loss_customers_key = Tag::getCurrentTagKey(Tag::ACTIVE_LIGHT_LOSS_CUSTOMERS_TAG);
        $none_order_customers_key = Tag::getCurrentTagKey(Tag::NONE_ORDER_CUSTOMERS);
        $already_order_customers_key = Tag::getCurrentTagKey(Tag::ALREADY_ORDER_CUSTOMERS);
        $active_reactive_customers_key = Tag::getCurrentTagKey(Tag::ACTIVE_REACTIVE_CUSTOMERS_TAG);

        $active_light_loss_customers = Tag::getMountedCustomersPro($active_light_loss_customers_key);
        $none_order_customers = Tag::getMountedCustomersPro($none_order_customers_key);
        $already_order_customers = Tag::getMountedCustomersPro($already_order_customers_key);
        $active_reactive_customers = Tag::getMountedCustomersPro($active_reactive_customers_key);

        $customer_focus = array_merge($active_light_loss_customers, $none_order_customers, $already_order_customers, $active_reactive_customers);
        $this->saveData(self::CLASSIFY_FOCUS, $customer_focus);

        //沉默用户统计  重度流失用户，僵尸用户
        $active_seriously_loss_customers_key = Tag::getCurrentTagKey(Tag::ACTIVE_SERIOUSLY_LOSS_CUSTOMERS_TAG);
        $zombie_customers_key = Tag::getCurrentTagKey(Tag::ZOMBIE_CUSTOMERS_TAG);

        $active_seriously_loss_customers = Tag::getMountedCustomersPro($active_seriously_loss_customers_key);
        $zombie_customers = Tag::getMountedCustomersPro($zombie_customers_key);

        $customer_silent = array_merge($active_seriously_loss_customers, $zombie_customers);

        $this->saveData(self::CLASSIFY_SILENT, $customer_silent);

        //本月已经下单
        $customer_already_order = LeCustomers::find()->where(['>', 'last_place_order_at', $this_month])->column();
        $this->saveData(self::CLASSIFY_MONTH_ORDERED, $customer_already_order);

        //本月未下单
        $customer_none_order = LeCustomers::find()->where(['<', 'last_place_order_at', $this_month])->column();
        $this->saveData(self::CLASSIFY_MONTH_NONE_ORDERED, $customer_none_order);

        Tools::log('complete', 'CustomerTagRelationStat.log');
    }

    public function saveData($classify, $data)
    {
        if (!empty($data)) {
            $save_data = [];
            foreach ($data as $customer_id) {
                $save_data[] = [
                    null,
                    $customer_id,
                    $classify,
                ];
            }
            //删除之前的统计结果
            CustomerTagRelation::deleteAll(['tag_id' => $classify]);
            \Yii::$app->customerDb->createCommand()->batchInsert(CustomerTagRelation::tableName(),
                CustomerTagRelation::getTableSchema()->getColumnNames(), $save_data)->execute();
        }
    }

}