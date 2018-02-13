<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/6
 * Time: 14:56
 */

namespace service\mq_processor\customer;

use service\models\customer\Observer;
use service\mq_processor\Processor;

/**
 * 商家登录事件处理
 * @package service\models\customer
 */
class ApprovedProcessor extends Processor
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        Observer::customerCreated($this->getValue());
        //Observer::autoApproved($this->getValue());
        Observer::customerInfoSyncToRelationship($this->getValue());
    }
}