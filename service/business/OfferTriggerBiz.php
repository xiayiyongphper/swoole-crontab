<?php

namespace service\business;

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/11
 * Time: 14:04
 */
use common\components\Sms;
use common\helpers\UserTagsHelper;
use common\models\common\OfferTrigger;
use common\models\merchant\LeMerchantTriggerMsg;
use framework\components\log\LogAbstract;
use common\helpers\Schema;
use common\models\core\Rule;
use common\models\customer\DeviceToken;
use common\models\customer\LeCustomer;
use common\redis\Keys;
use framework\components\ToolsAbstract;
use service\processes\MsgPushProcess;
use service\tasks\Ex;

/**
 * Class OfferTriggerBiz
 * @package service\business
 */
class OfferTriggerBiz
{
    /**
     * 配置的所有城市的优惠券。
     * 格式：
     * [
     *   'cityCode1' => [Rule[], Rule[]],
     *   'cityCode2' => [Rule[], Rule[]]
     * ]
     * 城市CODE为一维下标，值为该城市的所有优惠券列表
     * @var array
     */
    private $allCityCoupons;
    /**
     * @var LeCustomer[]
     */
    private $customers;

    /**
     * 配置所有用户的token信息
     * 格式：
     * [
     *   'customerId1' => DeviceToken[],
     *   'customerId2' => DeviceToken[]
     * ]
     * @var DeviceToken[]
     */
    private $customerTokens;

    /**
     * @var int|null
     */
    private $triggerCustomerId;

    /**
     * 触发的用户
     * @var LeCustomer
     */
    private $triggerCustomer;

    /**
     * @var OfferTrigger
     */
    private $offerTrigger;

    const TYPE_NORMAL = 1;
    const TYPE_ADD_BALANCE = 2;
    const TYPE_GRANT_COUPON = 3;

    /* ========= 下面参数对于当前用户的 START ======== */

    /**
     * 当前用户
     * @var LeCustomer
     */
    private $currentCustomer;

    /**
     * 当前用户的推送其他是否已经推送过了。比如发零钱/优惠券都会推送。
     * @var bool
     */
    private $hasOtherPushed = false;

    /**
     * 当前用户是否已经推送过自定义的消息了
     * @var bool
     */
    private $hasCustomMsgPushed = false;

    /**
     * 当前用户的模板参数。
     * 格式：[
     *  'param1' => 'value1',
     *  'param2' => 'value2'
     * ]
     * 目前keys有：
     * coupons_discount_sum：优惠券最大优惠总额
     * coupons_count：优惠券总数
     * balance_new：新的零钱金额
     * balance_added：新增的零钱金额
     * @var array
     */
    private $tplParams;

    /* ========= 下面参数对于当前用户的 END ======== */

    /**
     * OfferTriggerBiz constructor.
     * @param int $offerTriggerId
     * @param int|null $triggerCustomerId
     * @throws \Exception
     */
    public function __construct($offerTriggerId, $triggerCustomerId = null)
    {
        if (empty($offerTriggerId) || filter_var($offerTriggerId, FILTER_VALIDATE_INT) === false) {
            throw Ex::getException(Ex::EX_OFFER_TRIGGER_BASE);
        }

        /** @var OfferTrigger $offerTrigger */
        $offerTrigger = OfferTrigger::findOne(['entity_id' => $offerTriggerId]);
        if (!$offerTrigger) {
            throw Ex::getException(Ex::EX_OFFER_TRIGGER_BASE);
        }

        $this->offerTrigger = $offerTrigger;
        $this->triggerCustomerId = $triggerCustomerId;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function trigger()
    {
        $this->log('########### call trigger()!trigger_id=' . $this->offerTrigger->entity_id . '!###########');
        // 检查次数限制
        switch ($this->offerTrigger->trigger_scene) {
            case OfferTrigger::SCENE_TYPE_SINGLE_TIMING:
                $this->log('trigger_scene=SCENE_TYPE_SINGLE_TIMING');
                break;
            case OfferTrigger::SCENE_TYPE_MULTI_TIMING:
                $this->log('trigger_scene=SCENE_TYPE_MULTI_TIMING');
                break;
            case OfferTrigger::SCENE_TYPE_TRIGGER:
                $this->log('trigger_scene=SCENE_TYPE_TRIGGER');
                /* 顺序不能调整，先checkTriggerCustomer再checkTriggerLimit */
                if (!$this->checkTriggerCustomer()) {
                    return false;
                }
                if (!$this->checkTriggerLimit()) {
                    return false;
                }
                break;
            default:
                throw Ex::getException(Ex::EX_OFFER_TRIGGER_BASE);
        }

        // 执行结果
        $ret = $this->proccessResults();
        $this->log('########### end call trigger()! ###########');
        return $ret;
    }

    /**
     * @return bool
     */
    private function checkTriggerCustomer()
    {
        $customerId = $this->triggerCustomerId;
        if (empty($customerId) || filter_var($customerId, FILTER_VALIDATE_INT) === false) {
            return false;
        }

        if ($customer = LeCustomer::findOne(['entity_id' => $customerId])) {
            $this->triggerCustomer = $customer;
            return true;
        }
        return false;
    }

    /**
     * 检查限制
     */
    private function checkTriggerLimit()
    {
        $this->log('start checkTriggerLimit');

        if (empty($this->triggerCustomer)) {
            return false;
        }

        $config = $this->offerTrigger->settings;
        $dayLimitRedisKey = Keys::getOfferTriggerDayLimitKey(
            $this->offerTrigger->entity_id,
            $this->triggerCustomer->entity_id
        );
        $totalLimitRedisKey = Keys::getOfferTriggerTotalLimitKey(
            $this->offerTrigger->entity_id,
            $this->triggerCustomer->entity_id
        );
        // 每日和总限制
        $dayNum = (int)ToolsAbstract::getRedis()->get($dayLimitRedisKey);
        $totalNum = (int)ToolsAbstract::getRedis()->get($totalLimitRedisKey);
        $this->log('checkTriggerLimit.daynum=' . $dayNum);
        $this->log('checkTriggerLimit.totalNum=' . $totalNum);

        if ($config['day_limit'] != 0 && $dayNum >= $config['day_limit']) {
            $this->log('day_limit.return=false');
            return false;
        }
        if ($config['total_limit'] != 0 && $totalNum >= $config['total_limit']) {
            $this->log('total_limit.return=false');
            return false;
        }
        return true;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    private function proccessResults()
    {
        $this->log('===============start proccessResults===============');

        $settingCustomerIds = $this->getSettingCustomerIds();
        $config = $this->offerTrigger->settings;
        /** @var LeCustomer[] $customers */
        if (!$customers = $this->filterCustomers($settingCustomerIds)) {
            $this->log('filterCustomers is empty.');
            return true;
        }

        /* 检查参数 */
        $money = null;
        $couponIds = null;
        $grantCouponMaxNum = 0;
        $sms = null;
        $customerPushMsg = null;
        // 零钱
        if (isset($config['results_money']) && $config['results_money'] > 0 && $config['results_money'] <= 10) {
            $money = $config['results_money'];
        }
        // 优惠券
        if (!empty($config['results_coupons'])) {
            if ($couponIds = array_filter(explode(',', $config['results_coupons']))) {
                if ($config['results_coupon_grant_type'] == OfferTrigger::RESULT_COUPON_GRANT_TYPE_RANDOM) {
                    $grantCouponMaxNum = $config['results_coupon_grant_random_num'];
                } else {
                    $grantCouponMaxNum = PHP_INT_MAX;
                }
            }
        }
        // 短信
        if (!empty($config['results_sms'])) {
            $sms = $config['results_sms'];
        }
        // 推送
        if (isset($config['results_push_type'])
            && $config['results_push_type'] == OfferTrigger::RESULT_PUSH_TYPE_CUSTOM
        ) {
            $customerPushMsg = $config['results_push_custom'];
        }

        // 初始化公共参数
        $this->initCoupons($couponIds);
        $this->initCustomerTokens(array_keys($customers));

        /*
         * 根据用户遍历处理。
         * 先发零钱和优惠券，然后确定模板参数，根据模板参数去推送消息和发短信。
         */
        foreach ($customers as $customerId => $customer) {
            $this->log('**** 开始处理用户ID：' . $customerId . ' ****');
            // 初始化
            $this->hasCustomMsgPushed = false;
            $this->hasOtherPushed = false;
            $this->tplParams = [];
            // 保存当前用户信息
            $this->currentCustomer = $customer;
            // 如果是操作触发类型，验证用户后需要记录次数
            if ($this->offerTrigger->trigger_scene == OfferTrigger::SCENE_TYPE_TRIGGER) {
                $dayLimitRedisKey = Keys::getOfferTriggerDayLimitKey(
                    $this->offerTrigger->entity_id,
                    $this->triggerCustomer->entity_id
                );
                $totalLimitRedisKey = Keys::getOfferTriggerTotalLimitKey(
                    $this->offerTrigger->entity_id,
                    $this->triggerCustomer->entity_id
                );
                ToolsAbstract::getRedis()->incr($dayLimitRedisKey);
                ToolsAbstract::getRedis()->expire($dayLimitRedisKey, 24 * 3600);    // 24小时过期
                ToolsAbstract::getRedis()->incr($totalLimitRedisKey);
            }

            // 发零钱
            if ($money) {
                $this->addBalance($config['results_money'], $customer);
            }
            // 发优惠券
            if ($couponIds && $grantCouponMaxNum > 0) {
                $this->grantCoupons($customer, $grantCouponMaxNum);
            }
            // 发短信
            if ($sms) {
//                $this->sendSMS($sms, $customer);
            }
            // 发推送，如果其他没有推送过，就是推送自定义消息
            if (!$this->hasOtherPushed && $customerPushMsg != null) {
                $this->pushMessage($config['results_push_custom'], $customer);
            }

            $this->log('**** 结束处理用户ID：' . $customerId . ' ****');
        }
        $this->log('===============end proccessResults===============');
        return true;
    }

    /**
     * @param array $couponIds
     */
    private function initCoupons($couponIds)
    {
        $this->log('call initCoupons()');
        // 查出来过滤掉不是用户该城市的优惠券
        $coupons = Rule::getCouponRulesByRuleIdsCouponType($couponIds, [
            Rule::COUPON_TYPE_SPECIFIC,
            Rule::RULE_COUPON_SEND
        ]);

        if (!$coupons) {
            $this->log('no coupons!');
            return;
        }

        // 根据城市分组
        $this->allCityCoupons = [];
        /** @var Rule $coupon */
        foreach ($coupons as $coupon) {
            /** @var Rule[][] $allCityCoupons */
            $this->allCityCoupons[$coupon->city][] = $coupon;
        }
    }

    /**
     * @param int[] $customerIds
     * @return LeCustomer[]
     */
    private function filterCustomers($customerIds)
    {
        if (empty($this->triggerCustomer)) {
            $this->log('filterCustomers.triggerCustomer=empty');
            return $this->getCustomers($customerIds);
        } else if (in_array($this->triggerCustomer->entity_id, $customerIds)) {
            $this->log('filterCustomers.triggerCustomer not empty,match userId');
            return [$this->triggerCustomer->entity_id => $this->triggerCustomer];
        } else {
            $this->log('filterCustomers.triggerCustomer not emtpy,not match userId');
            return [];
        }
    }

    /**
     * 发零钱
     *
     * @param int $money
     * @param LeCustomer $customer
     * @param bool $pushMessage
     * @return bool
     *
     */
    private function addBalance($money, $customer, $pushMessage = true)
    {
        $this->log('call addBalance()!!!!!!!!!!, money=' . $money);
        // 更新零钱
//        $customer->balance += $money;
        $result = $customer->addBalance('系统赠送', 'ADDITIONAL_PACKAGE_TO_BALANCE', $money);
        // 记录参数
        if ($result) {
            $this->log('addBalance success!');

            $this->tplParams['balance_new'] = $customer->balance;
            $this->tplParams['balance_added'] = $money;

            // 如果是操作触发，则记录到LeMerchantTriggerMsg
            if ($this->offerTrigger->trigger_scene == OfferTrigger::SCENE_TYPE_TRIGGER
                && $this->offerTrigger->trigger_type == OfferTrigger::TRIGGER_TYPE_ORDER_CREATED
            ) {
                $this->save2MerchantTriggerMsg($customer, LeMerchantTriggerMsg::TYPE_BALANCE, ['balance' => $money]);
            }
        }

        // 推送
        if ($pushMessage) {
            $this->hasOtherPushed = true;
            $this->pushMessage($this->getPushMessage(self::TYPE_ADD_BALANCE), $customer, Schema::getWalletSchema());
        }
        return true;
    }

    /**
     * @param int $type
     * @return string|null
     */
    private function getPushMessage($type)
    {
        $this->log('getPushMessage()!!!!!!!!!!,type=' . $type);
        $config = $this->offerTrigger->settings;
        if (isset($config['results_push_type'])
            && $config['results_push_type'] == OfferTrigger::RESULT_PUSH_TYPE_CUSTOM
        ) {
            if ($this->hasCustomMsgPushed) {
                $this->log('getPushMessage(),results_push_custom=true,hasCustomMsgPushed=true');
                return null;    // 如果是null，则不会推送消息
            } else {
                $this->hasCustomMsgPushed = true;   // 先放这里吧
                $this->log('getPushMessage(),results_push_custom=true');
                return $this->formatTpl($this->offerTrigger->settings['results_push_custom']);
            }
        } else {
            $ret = null;
            $this->log('getPushMessage(),results_push_custom=false');
            switch ($type) {
                case self::TYPE_ADD_BALANCE:
                    $ret = '您获得了#balance_added#元零钱，快来看看吧';
                    break;
                case self::TYPE_GRANT_COUPON:
                    $ret = '您获得价值#coupons_discount_sum#元优惠券，快来看看吧';
                    break;
            }
            return $this->formatTpl($ret);
        }
    }

    /**
     * @param string $tpl
     * @return string
     */
    private function formatTpl($tpl)
    {
        $tplParams = $this->tplParams;
        return preg_replace_callback('|\#(\w+)\#|', function ($matches) use ($tplParams) {
            if (!empty($matches[1]) && isset($tplParams[$matches[1]])) {
                return $tplParams[$matches[1]];
            } else {
                return ' ';
            }
        }, $tpl);
    }

    /**
     * 发放优惠券
     *
     * @param LeCustomer $customer
     * @param int $num 数量，0：全部
     * @param bool $pushMessage
     * @return bool
     */
    private function grantCoupons($customer, $num = 0, $pushMessage = true)
    {
        $this->log('call grantCoupons()!!!!!!!!!!');

        $allCityCoupons = $this->allCityCoupons;
        /** @var LeCustomer $customer */
        if (!isset($allCityCoupons[$customer->city])
            || ($cityCouponNum = count($allCityCoupons[$customer->city])) <= 0
        ) {
            $this->log('no coupons!city=' . $customer->city);
            return true;
        }

        $hasPushed = false;
        $index = 0;
        $loop = 0;
        $leftNum = $num;
        $cityCoupons = $allCityCoupons[$customer->city];
        $grantCoupons = [];
        // 随机index
        if ($num < $cityCouponNum) {
            $index = rand(0, $cityCouponNum - 1);
        }

        $this->log(sprintf('index=%s,cityCouponNum=%s', $index, $cityCouponNum));
        $this->log('start grant coupon to user ' . $customer->entity_id);

        /** @var Rule $coupon */
        for ($i = $index; /**/;/**/) {
            try {
                $i = ($i + 1) % $cityCouponNum;
                $this->log(sprintf('i=%s,leftNum=%s,loop=%s,couponNum=%s', $i, $leftNum, $loop, $cityCouponNum));
                // 如果异常，则说明没有发放成功，不能减掉
                if ($leftNum <= 0 || $loop++ >= $cityCouponNum) {
                    break;
                }

                // 发放优惠券
                Rule::getCoupon($cityCoupons[$i], $customer->entity_id, 1);
                $leftNum--; // 减去一个
                $grantCoupons[$cityCoupons[$i]->rule_id] = $cityCoupons[$i]->max_discount_value;  // 记录发放了的优惠券ID

                // 记录日志
                $this->log(sprintf(
                    'getCoupon(),customer_id=%s,coupon_id=%s',
                    $customer->entity_id,
                    $cityCoupons[$i]->rule_id
                ));
            } catch (\Throwable $e) {
                $this->log($e->__toString());
            }
        }

        if ($grantCoupons) {
            // 如果是操作触发且是下单成功，则记录到LeMerchantTriggerMsg
            if ($this->offerTrigger->trigger_scene == OfferTrigger::SCENE_TYPE_TRIGGER
                && $this->offerTrigger->trigger_type == OfferTrigger::TRIGGER_TYPE_ORDER_CREATED
            ) {
                $this->save2MerchantTriggerMsg($customer, LeMerchantTriggerMsg::TYPE_COUPON,
                    ['coupons' => $grantCoupons]);
            }
            // 保存模板参数
            $this->tplParams['coupons_count'] = count($grantCoupons);
            $this->tplParams['coupons_discount_sum'] = number_format(array_sum($grantCoupons), 2, '.', '');

            // 推送
            if ($pushMessage) {
                $this->hasOtherPushed = true;
                $this->pushMessage(
                    $this->getPushMessage(self::TYPE_GRANT_COUPON),
                    $customer,
                    Schema::getCouponListSchema()
                );
            }
        }

        $this->log('end grant coupon to user ' . $customer->entity_id);
        return true;
    }

    /**
     * 发短信
     *
     * @param string $tplId
     * @param LeCustomer $customer
     * @param int $type
     * @return bool
     */
    private function sendSMS($tplId, $customer, $type = self::TYPE_NORMAL)
    {
        $this->log('call sendSMS()!!!!!!!!!!');

        $params = [];
        switch ($type) {
            case self::TYPE_NORMAL:
                break;
            case self::TYPE_ADD_BALANCE:
                $params = [];   // 确认模板和参数 !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                break;
            case self::TYPE_GRANT_COUPON:
                $params = [];   // 确认模板和参数 !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                break;
        }

        // 循环发送
        try {
            $this->log(sprintf('phone=%s,tpl=%s,param=%s', $customer->phone, $tplId, print_r($params, 1)));
            if (strlen($customer->phone) == 11) {
                Sms::send($customer->phone, $tplId, $params);
            } else {
                $this->log('seems not a phone number');
            }
        } catch (\Exception $e) {
            $this->log('发送短信异常' . $e->getMessage());
        }
        return true;
    }

    /**
     * 推送消息
     *
     * @param string $msg
     * @param LeCustomer $customer
     * @param string $scheme
     * @param string $title
     * @return bool
     */
    private function pushMessage($msg, $customer, $scheme = 'lelaishop://', $title = '乐来订货网')
    {
        $this->log('call pushMessage()!!!!!!!!!!!!!!!!!!!');

        if ($msg === null || !is_scalar($msg) || strlen($msg) <= 0 || preg_match('|\#(\w+)\#|', $msg)) {
            $this->log('invalid msg');
            return false;
        }

        // 没有token则直接跳过
        if (empty($this->customerTokens[$customer->entity_id])) {
            return false;
        }

        // 整理消息格式
        $token = $this->customerTokens[$customer->entity_id];
        $pushMsgArr = [
            'system' => $token->system,
            'token' => $token->token,
            'platform' => 1,    // 全部
            'channel' => $token->channel,
            'value_id' => $customer->entity_id,
            'typequeue' => $token->typequeue,
            'params' => [
                'title' => $title,
                'content' => $msg,
                'scheme' => $scheme
            ]
        ];

        $pushData = serialize($pushMsgArr);
        // 记录到日志
        $this->log($pushData);
        // 推送到redis
        try {
            ToolsAbstract::getRedis()->rPush(MsgPushProcess::MESSAGE_PUSH_QUEUE, $pushData);
        } catch (\Exception $e) {
            $this->log('推送消息到redis异常');
        }
        return true;
    }

    /**
     * @throws \Exception
     * @return array
     */
    private function getSettingCustomerIds()
    {
        $config = $this->offerTrigger->settings;
        if ($config['users_type'] == OfferTrigger::USER_TYPE_AREAS_AND_TAGS) {  // 根据区域和分群TAG
            if (empty($config['users_tags']) || empty($config['users_area_ids'])) {
                return [];
            }
            $tagIds = $config['users_tags'];
            $areaIds = $config['users_area_ids'];
            // 获取Tag的用户ID，单个用户触发事件的不获取全部用户ID
            if ($this->triggerCustomerId) {
                if (!UserTagsHelper::getIsCustomerIdInTagIds($this->triggerCustomerId, $tagIds)) {
                    $this->log('getIsCustomerIdInTagIds=0');
                    return [];
                }
                $this->log('getIsCustomerIdInTagIds=1');
                $customerIds = [$this->triggerCustomerId];
            } else {
                $customerIds = UserTagsHelper::getIsCustomerIdInTagIds($tagIds);
                $this->log('tags.count=' . count($tagIds) . ',customer_ids.count=' . count($customerIds));
            }
            // 过滤掉不是指定区域的
            $queryResults = LeCustomer::find()->select('entity_id')
                ->where(['entity_id' => $customerIds, 'area_id' => $areaIds])->asArray()->all();

            if (!$queryResults) {
                return [];
            } else {
                return array_column($queryResults, 'entity_id');
            }
        } elseif ($config['users_type'] == OfferTrigger::USER_TYPE_CITIES_AND_USER_IDS) {   // 根据城市和分群TAG
            return array_filter(explode(',', $config['users_ids']));
        } else {
            throw Ex::getException(Ex::EX_OFFER_TRIGGER_BASE);
        }
    }

    /***
     * @param int|int[] $customerIds
     * @return LeCustomer[]
     */
    private function getCustomers($customerIds)
    {
        if (!$this->customers) {
            $customers = LeCustomer::find()->select('*')->where([
                'entity_id' => $customerIds
            ])->all();

            if ($customers) {
                /** @var LeCustomer $customer */
                foreach ($customers as $customer) {
                    $this->customers[$customer->entity_id] = $customer;
                }
            }
        }
        return $this->customers ? $this->customers : [];
    }

    /**
     * @param int|int[] $customerIds
     * @return array|DeviceToken[]|\yii\db\ActiveRecord[]
     */
    private function initCustomerTokens($customerIds)
    {
        if (!$this->customerTokens) {
            $customerTokens = DeviceToken::find()->select('*')->where([
                'customer_id' => $customerIds
            ])->all();

            if ($customerTokens) {
                /** @var DeviceToken $customerToken */
                foreach ($customerTokens as $customerToken) {
                    $this->customerTokens[$customerToken->customer_id] = $customerToken;
                }
            }
        }
        return $this->customerTokens ? $this->customerTokens : [];
    }

    /**
     * @param LeCustomer $customer
     * @param int $msgType
     * @param array $result
     * @return bool
     */
    private function save2MerchantTriggerMsg($customer, $msgType, array $result = [])
    {
        $this->log('call save2MerchantTriggerMsg()');
        $triggerMsg = new LeMerchantTriggerMsg();
        $triggerMsg->status = LeMerchantTriggerMsg::STATUS_UNREAD;
        $triggerMsg->trigger_type = $this->offerTrigger->trigger_type;
        $triggerMsg->customer_id = $customer->entity_id;
        $triggerMsg->type = $msgType;
        $triggerMsg->result = json_encode($result);
        return $triggerMsg->save();
    }

    /**
     * @param $msg
     */
    public function log($msg)
    {
        $pid = function_exists('posix_getpid') ? posix_getpid() : '';
        ToolsAbstract::log($msg, 'OfferTriggerBiz-p' . $pid . '.log');
    }
}