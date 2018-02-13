<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/6
 * Time: 17:46
 */

namespace service\mq_processor\order;


use service\models\customer\Observer;
use service\mq_processor\Processor;

class ManualReturnChangeProcessor extends Processor
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
        Observer::return_balance($this->order);
    }
}