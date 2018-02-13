<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午4:34
 */

namespace service\mq_processor\order;

use common\helpers\SaasHelper;
use service\mq_processor\Processor;

class HoldedProcessor extends Processor
{
    public function run($data)
    {
        SaasHelper::notifySaas($data);
    }
}