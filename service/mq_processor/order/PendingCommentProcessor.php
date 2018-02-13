<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/13
 * Time: 14:37
 */

namespace service\mq_processor\order;

use common\helpers\OfferTriggerHelper;
use common\helpers\SaasHelper;
use service\models\merchant\Observer;
use service\mq_processor\Processor;

/**
 * 订单收货事件
 * @see MQAbstract::MSG_ORDER_PENDING_COMMENT
 * @package service\mq_processor\order
 */
class PendingCommentProcessor extends Processor
{
    private $order;

    /**
     * @inheritdoc
     */
    public function run($data)
    {
        $value = $this->getValue();
        $this->order = isset($value['order']) ? $value['order'] : [];

        $this->customerEvents();
        $this->merchantEvents();

        OfferTriggerHelper::triggeredByOrderPendingComment($this->getValue(), $this->getMqMsgId());
        SaasHelper::notifySaas($data);
    }

    private function customerEvents()
    {
        \service\models\customer\Observer::rebates_add($this->order);
        \service\models\customer\Observer::additional_package_to_balance($this->order);
    }

    private function merchantEvents()
    {
        Observer::orderComplete($this->order);
    }
}
