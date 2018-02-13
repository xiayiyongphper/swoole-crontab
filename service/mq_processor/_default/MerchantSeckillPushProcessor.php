<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/6
 * Time: 17:50
 */

namespace service\mq_processor\_default;


use service\models\customer\Observer;
use service\mq_processor\Processor;

class MerchantSeckillPushProcessor extends Processor
{
    public function run($data)
    {
        $this->customerEvents();
    }

    private function customerEvents()
    {
        Observer::seckill_push($this->getValue());
    }
}