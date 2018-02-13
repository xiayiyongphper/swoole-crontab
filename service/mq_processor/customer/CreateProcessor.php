<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/13
 * Time: 14:31
 */

namespace service\mq_processor\customer;


use common\helpers\OfferTriggerHelper;
use service\models\customer\Observer;
use service\mq_processor\Processor;

/**
 * 商家创建并审核通过后的事件
 * @package service\models\customer
 */
class CreateProcessor extends Processor
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        Observer::customerCreated($data);
        OfferTriggerHelper::triggeredByCustomerRegister($this->getValue(), $this->getMqMsgId());
    }
}