<?php
namespace common\helpers;

use common\models\common\CrontabMQMsg;
use common\models\common\OfferTrigger;
use framework\components\ToolsAbstract;
use service\business\OfferTriggerBiz;

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/9
 * Time: 18:29
 */
class OfferTriggerHelper
{
    const LOG_FILE = 'OfferTriggerHelper.log';

    /**
     * @param array $data
     * @param int $msgId
     * @return mixed
     */
    public static function triggeredByOrderNew($data, $msgId = null)
    {
        try {
            $result = false;
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'begin']);
            if (!empty($data['order']['customer_id'])) {
                $result = self::execRequest($data['order']['customer_id'],
                    OfferTrigger::TRIGGER_TYPE_ORDER_CREATED, $msgId);
            }
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'end', 'result' => $result]);
            return $result;
        } catch (\Exception $e) {
            self::log($e->__toString());
        }
        return false;
    }

    /**
     * @param array $data
     * @param int $msgId
     * @return mixed
     */
    public static function triggeredByOrderPendingComment($data, $msgId = null)
    {
        try {
            $result = false;
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'begin']);
            if (!empty($data['order']['customer_id'])) {
                $result = self::execRequest($data['order']['customer_id'],
                    OfferTrigger::TRIGGER_TYPE_ORDER_RECEIPT, $msgId);
            }
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'end', 'result' => $result]);
            return $result;
        } catch (\Exception $e) {
            self::log($e->__toString());
        }
        return false;
    }

    /**
     * @param array $data
     * @param int $msgId
     * @return mixed
     */
    public static function triggeredByOrderComment($data, $msgId = null)
    {
        try {
            $result = false;
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'begin']);
            if (!empty($data['order']['customer_id'])) {
                $result = self::execRequest($data['order']['customer_id'],
                    OfferTrigger::TRIGGER_TYPE_ORDER_COMMENT, $msgId);
            }
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'end', 'result' => $result]);
            return $result;
        } catch (\Exception $e) {
            self::log($e->__toString());
        }
        return false;
    }

    /**
     * @param array $data
     * @param int $msgId
     * @return mixed
     */
    public static function triggeredByEnterMerchantHomePage($data, $msgId = null)
    {
        try {
            $result = false;
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'begin']);
            if (!empty($data['customer_id'])) {
                $result = self::execRequest($data['customer_id'], OfferTrigger::TRIGGER_TYPE_HOME_PAGE, $msgId);
            }
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'end', 'result' => $result]);
            return $result;
        } catch (\Exception $e) {
            self::log($e->__toString());
        }
        return false;
    }

    /**
     * @param array $data
     * @param int $msgId
     * @return mixed
     */
    public static function triggeredByCustomerLogin($data, $msgId = null)
    {
        try {
            $result = false;
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'begin']);
            if (!empty($data['entity_id'])) {
                $result = self::execRequest($data['entity_id'], OfferTrigger::TRIGGER_TYPE_USER_LOGIN, $msgId);
            }
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'end', 'result' => $result]);
            return $result;
        } catch (\Exception $e) {
            self::log($e->__toString());
        }
        return false;
    }

    /**
     * @param array $data
     * @param int $msgId
     * @return mixed
     */
    public static function triggeredByCustomerRegister($data, $msgId = null)
    {
        try {
            $result = false;
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'begin']);
            if (!empty($data['entity_id'])) {
                $result = self::execRequest($data['entity_id'], OfferTrigger::TRIGGER_TYPE_USER_REGISTER, $msgId);
            }
            CrontabMQMsg::trace($msgId, [__FUNCTION__ => 'end', 'result' => $result]);
            return $result;
        } catch (\Exception $e) {
            self::log($e->__toString());
        }
        return false;
    }

    /**
     * @param int $userId
     * @param int $triggerType
     * @param int $msgId
     * @return bool
     */
    private static function execRequest($userId, $triggerType, $msgId = null)
    {
        self::log('execRequest.userId=' . $userId . ',triggerType=' . $triggerType . ',msg_id=' . $msgId);

        $curDateTime = ToolsAbstract::getDate()->date();
        $triggers = OfferTrigger::find()->where([
            'status' => OfferTrigger::STATUS_ENABLED,
            'trigger_scene' => OfferTrigger::SCENE_TYPE_TRIGGER,
            'trigger_type' => $triggerType,
        ])->andWhere(['<=', 'from_time', $curDateTime])
            ->andWhere(['>=', 'to_time', $curDateTime])
            ->all();

        if (!$triggers) {
            self::log('no triggers!');
            return true;
        }
        return self::execRequestByLocal($triggers, $userId, $msgId);
    }

    /**
     * @param OfferTrigger[] $triggers
     * @param int $userId
     * @param int $msgId
     * @return int 成功的数量
     */
    private static function execRequestByLocal(array $triggers, $userId, $msgId = null)
    {
        self::log('execRequestByLocal(),__msg_id__=' . $msgId);
        $successNum = 0;
        /** @var OfferTrigger $trigger */
        foreach ($triggers as $trigger) {
            try {
                self::log('trigger_id=' . $trigger->entity_id . ',__msg_id__=' . $msgId);
                if ($result = (new OfferTriggerBiz($trigger->entity_id, $userId))->trigger()) {
                    $successNum++;
                }
                self::log('execRequestByLocal=OK,result=' . print_r($result, 1) . ',__msg_id__=' . $msgId);
            } catch (\Exception $e) {
                self::log('execRequestByLocal=FAILED');
                self::log($e->__toString());
            }
        }
        self::log('end execRequestByLocal(),__msg_id__=' . $msgId);
        return $successNum;
    }

    /**
     * @param $msg
     */
    private static function log($msg)
    {
        ToolsAbstract::log($msg, self::LOG_FILE);
    }
}