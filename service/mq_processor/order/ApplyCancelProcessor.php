<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午4:34
 */

namespace service\mq_processor\order;

use common\helpers\SaasHelper;
use service\models\merchant\Observer;
use service\mq_processor\Processor;

class ApplyCancelProcessor extends Processor
{
    public function run($data)
    {
        $value = $this->getValue();
        Observer::orderApplyCancel($value['order']);
        SaasHelper::notifySaas($data);
    }
}