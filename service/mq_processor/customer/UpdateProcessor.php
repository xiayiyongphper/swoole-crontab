<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/13
 * Time: 11:46
 */

namespace service\mq_processor\customer;

use common\helpers\OfferTriggerHelper;
use service\models\customer\Observer;
use service\mq_processor\Processor;

/**
 * 商家登录事件处理
 * @package service\models\customer
 */
class UpdateProcessor extends Processor
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        Observer::customerInfoSyncToRelationship($this->getValue());
    }
}