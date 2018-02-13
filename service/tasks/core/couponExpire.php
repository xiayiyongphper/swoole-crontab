<?php
/**
 * 供货商综合得分规则
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 11:22
 */

namespace service\tasks\core;

use common\models\core\Rule;
use common\models\core\UserCoupon;
use framework\components\ProxyAbstract;
use framework\components\ToolsAbstract;
use service\tasks\TaskService;


class couponExpire extends TaskService
{
    /**
     * @param mixed $data
     * 发送即将过期的优惠券提醒
     * @return mixed|void
     */
    public function run($data)
    {
        $expireDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +3 days'));
        $now = date('Y-m-d H:i:s');
//        print_r($expireDate);
        $coupons = UserCoupon::find()
            ->where(['state' => UserCoupon::USER_COUPON_UNUSED])
            ->andWhere(['<', 'expiration_date', $expireDate])
//            ->groupBy('customer_id')
            ->andWhere(['>', 'expiration_date', $now])
            ->all();
        if (count($coupons) == 0) {
            print_r('无即将过期优惠券需要推送');
        }
        $rules = [];
        $customers = [];
        //user coupons
        /** @var UserCoupon $coupon */
        foreach ($coupons as $coupon) {
            if (!isset($rules[$coupon->rule_id])) {
                /** @var Rule $rule */
                $rule = Rule::find()->where(['rule_id' => $coupon->rule_id])->one();
                $rules[$coupon->rule_id] = $rule;
            }
            $customers[$coupon->customer_id]['num'] = isset($customers[$coupon->customer_id]['num']) ?
                $customers[$coupon->customer_id]['num']++ : 1;
            $rule = $rules[$coupon->rule_id];
            if ($rule->simple_action == 'by_percent') {
                $arr = array_filter(explode(',', $rule->discount_amount));
                $percent = array_pop($arr);
                $value = (100 - $percent) * 10;  //满折按1000块进行计算
            } else if ($rule->simple_action == 'by_fixed') {
                $arr = array_filter(explode(',', $rule->discount_amount));
                $off_money = array_pop($arr);
                $value = $off_money;
            } else {
                $value = 5;
            }

            $customers[$coupon->customer_id]['value'] = isset($customers[$coupon->customer_id]['value']) ?
                $customers[$coupon->customer_id]['value'] += $value : $value;
            $coupon->expire_push = 1;
            $coupon->save();
        }


        $name = 'coupon_expire';
        $eventName = sprintf('%s_msg.%s', 'customer', $name);
        foreach ($customers as $customer_id => $customerData) {
            $customerData['customer_id'] = $customer_id;
            $event = [
                'name' => $name,
                'data' => $customerData,
            ];
            ToolsAbstract::log($customerData, 'actionCouponExpire.log');
            ProxyAbstract::sendMessage($eventName, $event);
        }
    }
}