<?php

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/13
 * Time: 14:43
 */

namespace service\mq_processor\product;

use framework\mq\MQAbstract;
use service\models\merchant\Observer;
use service\mq_processor\Processor;

/**
 * @see MQAbstract::MSG_PRODUCT_DELETE
 * @package service\mq_processor\product
 */
class DeleteProcessor extends Processor
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        $value = $this->getValue();
        Observer::productDelete($value);
    }
}