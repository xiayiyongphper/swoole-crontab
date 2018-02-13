<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/13
 * Time: 14:37
 */

namespace service\mq_processor\order;

use common\helpers\OfferTriggerHelper;
use framework\mq\MQAbstract;
use service\mq_processor\Processor;

/**
 * 订单评价事件
 * @see MQAbstract::MSG_ORDER_COMMENT
 * @package service\mq_processor\order
 */
class CommentProcessor extends Processor
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        return OfferTriggerHelper::triggeredByOrderComment($this->getValue(), $this->getMqMsgId());
    }
}