<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/6
 * Time: 17:44
 */

namespace service\mq_processor\order;


use service\models\customer\Observer;
use service\mq_processor\Processor;

class ManualRebateProcessor extends Processor
{
    private $order;

    public function run($data)
    {
        $value = $this->getValue();
        $this->order = isset($value['order']) ? $value['order'] : [];

        $this->customerEvents();
    }

    private function customerEvents()
    {
        Observer::rebates_add($this->order);
        Observer::additional_package_to_balance($this->order);
    }
}