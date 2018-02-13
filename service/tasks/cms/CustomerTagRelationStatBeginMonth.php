<?php

namespace service\tasks\cms;


use common\helpers\Tag;
use common\helpers\Tools;
use common\models\CustomerTagRelation;
use common\models\LeCustomers;
use service\tasks\TaskService;

/**
 * Author Jason Y.Wang
 * Class CustomerTagRelationStatBeginMonth
 * @package service\resources\crontab\v1
 *
 * 供业务员筛选超市时使用
 * 每月一号跑一次,在CustomersAllTagStat之后跑
 * 业务员V1.6版本加入
 */
class CustomerTagRelationStatBeginMonth extends TaskService
{

    const CLASSIFY_IMPORTANT_VISIT = 2; //重点拜访用户
    const CLASSIFY_ACTIVE_RISE_BEGIN_MONTH = 3; //月初活跃上升用户
    const CLASSIFY_CONTINUE_ACTIVE = 4; //持续活跃用户
    const CLASSIFY_SILENT_BEGIN_MONTH_NOT_VISIT = 5; //月初为沉睡用户30天内无拜访
    const CLASSIFY_SILENT_BEGIN_MONTH_VISITED = 6; //30天内有拜访，月初仍未沉睡用户

    public function run($data = null)
    {

        Tools::log('start', 'CustomerTagRelationStatBeginMonth.log');

        $redis = Tools::getRedis();

        $timeStamp = Tools::getDate()->timestamp();

        $this_month_first_day = date('Y-m-01', $timeStamp);
        $last_month_first_day = date('Y-m-01', strtotime('-1 month', $timeStamp));


        //轻度流失用户
        $this_month_active_light_loss_customers_key = Tag::getHistoryTagKey(Tag::ACTIVE_LIGHT_LOSS_CUSTOMERS_TAG, $this_month_first_day);
        $this_month_active_light_loss_customers = Tag::getMountedCustomersPro($this_month_active_light_loss_customers_key);

        //未下单新用户
        $this_month_none_order_customers_key = Tag::getHistoryTagKey(Tag::NONE_ORDER_CUSTOMERS, $this_month_first_day);
        $none_order_customers = Tag::getMountedCustomersPro($this_month_none_order_customers_key);

        //已下单新用户
        $this_month_already_order_customers_key = Tag::getHistoryTagKey(Tag::ALREADY_ORDER_CUSTOMERS, $this_month_first_day);
        $this_month_already_order_customers = Tag::getMountedCustomersPro($this_month_already_order_customers_key);

        //重新激活用户
        $this_month_active_reactive_customers_key = Tag::getHistoryTagKey(Tag::ACTIVE_REACTIVE_CUSTOMERS_TAG, $this_month_first_day);
        $this_month_active_reactive_customers = Tag::getMountedCustomersPro($this_month_active_reactive_customers_key);


        //重点流失用户
        $this_month_active_seriously_loss_customers_key = Tag::getHistoryTagKey(Tag::ACTIVE_SERIOUSLY_LOSS_CUSTOMERS_TAG, $this_month_first_day);
        $this_month_active_seriously_loss_customers = Tag::getMountedCustomersPro($this_month_active_seriously_loss_customers_key);

        //僵尸用户
        $this_month_zombie_customers_key = Tag::getHistoryTagKey(Tag::ZOMBIE_CUSTOMERS_TAG, $this_month_first_day);
        $this_month_zombie_customers = Tag::getMountedCustomersPro($this_month_zombie_customers_key);

        //上月一号活跃上升或活跃下降
        //上月一号活跃上升用户
        $last_month_customer_rise_key = Tag::getHistoryTagKey(Tag::ACTIVE_RISE_CUSTOMERS_TAG, $last_month_first_day);
        $last_month_customer_rise = Tag::getMountedCustomersPro($last_month_customer_rise_key);

        //上月一号活跃下降用户
        $last_month_customer_decline_key = Tag::getHistoryTagKey(Tag::ACTIVE_DECLINE_CUSTOMERS_TAG, $last_month_first_day);
        $last_month_customer_decline = Tag::getMountedCustomersPro($last_month_customer_decline_key);

        //本月一号活跃上升或活跃下降
        //本月一号活跃上升用户
        $this_month_customer_rise_key = Tag::getHistoryTagKey(Tag::ACTIVE_RISE_CUSTOMERS_TAG, $this_month_first_day);
        $this_month_customer_rise = Tag::getMountedCustomersPro($this_month_customer_rise_key);

        //本月一号活跃下降用户
        $this_month_customer_decline_key = Tag::getHistoryTagKey(Tag::ACTIVE_DECLINE_CUSTOMERS_TAG, $this_month_first_day);
        $this_month_customer_decline = Tag::getMountedCustomersPro($this_month_customer_decline_key);


        //重点拜访用户统计  轻度流失用户，未下单新用户，已下单新用户，重新激活用户
        $customer_focus = array_merge($this_month_active_light_loss_customers, $none_order_customers, $this_month_already_order_customers, $this_month_active_reactive_customers);
        $this->saveData(self::CLASSIFY_IMPORTANT_VISIT, $customer_focus);

        //月初上升至活跃用户  上月一号为其他类型客户，本月一号为活跃上升or活跃下降
        //上月一号为其他类型客户
        $redis->bitOp('OR', 'customer_last_month_key_tmp', $last_month_customer_rise, $last_month_customer_decline);
        $redis->bitOp('NOT', 'customer_rise_last_month_key_not_tmp', 'customer_last_month_key_tmp');
        $redis->bitOp('OR', 'customer_this_month_key_tmp', $this_month_customer_rise, $this_month_customer_decline);
        $redis->bitOp('and', 'customer_rise_this_month_first_day', 'customer_rise_last_month_key_not_tmp', 'customer_this_month_key_tmp');
        $customer_rise_this_month_first_day_ids = Tag::getMountedCustomersPro('customer_rise_this_month_first_day');
        $this->saveData(self::CLASSIFY_ACTIVE_RISE_BEGIN_MONTH, $customer_rise_this_month_first_day_ids);
        $redis->expire('customer_last_month_key_tmp', 60);
        $redis->expire('customer_rise_last_month_key_not_tmp', 60);
        $redis->expire('customer_this_month_key_tmp', 60);
        $redis->expire('customer_rise_this_month_first_day', 60);


        //持续活跃用户 上月一号为活跃上升or活跃下降  本月一号为活跃上升or活跃下降
        $continue_active_customer_last_month = array_merge($last_month_customer_rise, $last_month_customer_decline);
        $continue_active_customer_this_month = array_merge($this_month_customer_rise, $this_month_customer_decline);
        $continue_active_customer = array_intersect($continue_active_customer_last_month, $continue_active_customer_this_month);
        $this->saveData(self::CLASSIFY_CONTINUE_ACTIVE, $continue_active_customer);

        //沉睡用户
        $sleep_customer = array_merge($this_month_active_seriously_loss_customers, $this_month_zombie_customers);
        //沉睡用户30内无拜访  本月一号为重度流失用户or僵尸用户，且30天内无任何拜访
        $no_visit_in_30_day = LeCustomers::find()->select('entity_id')->where(['<', 'last_visited_at', $last_month_first_day])->column();
        $sleep_and_no_visit_in_30_day = array_intersect($sleep_customer, $no_visit_in_30_day);
        $this->saveData(self::CLASSIFY_SILENT_BEGIN_MONTH_NOT_VISIT, $sleep_and_no_visit_in_30_day);


        //沉睡用户30内有拜访  本月一号为重度流失用户or僵尸用户，且30天有拜访
        $visited_in_30_day = LeCustomers::find()->select('entity_id')->where(['>', 'last_visited_at', $last_month_first_day])->column();
        $sleep_and_visited_in_30_day = array_intersect($sleep_customer, $visited_in_30_day);
        $this->saveData(self::CLASSIFY_SILENT_BEGIN_MONTH_VISITED, $sleep_and_visited_in_30_day);

        Tools::log('complete', 'CustomerTagRelationStatBeginMonth.log');
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