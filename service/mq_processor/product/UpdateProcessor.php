<?php

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/13
 * Time: 14:43
 */

namespace service\mq_processor\product;

use common\helpers\Tools;
use framework\mq\MQAbstract;
use service\models\merchant\Observer;
use service\mq_processor\Processor;

/**
 * @see MQAbstract::MSG_GROUP_SUB_PRODUCT_UPDATE
 * @package service\mq_processor\product
 */
class UpdateProcessor extends Processor
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        Tools::log($data,'UpdateProcessor.log');
        $value = $this->getValue();
        Observer::productUpdate($value);
        Observer::updateGroupProductStocksOnProUpdate($value);
    }
}