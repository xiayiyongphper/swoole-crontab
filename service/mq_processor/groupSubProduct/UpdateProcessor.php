<?php

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/13
 * Time: 14:43
 */

namespace service\mq_processor\groupSubProduct;

use framework\mq\MQAbstract;
use service\models\merchant\Observer;
use service\mq_processor\Processor;

/**
 * 用户进入首页事件
 * @see MQAbstract::MSG_PRODUCT_UPDATE
 * @package service\mq_processor\product
 */
class UpdateProcessor extends Processor
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        $value = $this->getValue();
        Observer::updateGroupProductStockOnSubProductUpdate($value);
    }
}