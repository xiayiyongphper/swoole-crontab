<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午4:34
 */

namespace service\mq_processor\order;

use framework\components\ToolsAbstract;
use service\models\core\Observer;
use service\mq_processor\Processor;

class ManualReturnCouponProcessor extends Processor
{
    public function run($data)
    {
        $this->coreEvents();
    }

    private function coreEvents()
    {
        try {
            //退优惠券
            Observer::returnCoupon($this->getValue());
        } catch (\Exception $e) {
            $this->log($e->__toString());
            ToolsAbstract::logException($e);
        } catch (\Error $error) {
            $this->log($error->__toString());
            ToolsAbstract::logError($error);
        }
    }
}