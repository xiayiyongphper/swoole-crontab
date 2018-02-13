<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午4:34
 */

namespace service\mq_processor\order;

use common\helpers\SaasHelper;
use service\models\customer\Observer;
use service\mq_processor\Processor;

class OrderRejectCancelProcessor extends Processor
{
    private $order;

    public function run($data)
    {
        $value = $this->getValue();
        $this->order = isset($value['order']) ? $value['order'] : [];

        $this->customerEvents();

        SaasHelper::notifySaas($data);
    }

    private function customerEvents()
    {
        Observer::orderConfirm($this->order);
    }
}