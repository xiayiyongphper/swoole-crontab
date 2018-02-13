<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午4:34
 */

namespace service\mq_processor\order;

use common\helpers\SaasHelper;
use service\models\core\Observer;
use service\mq_processor\Processor;

class RebateSuccessProcessor extends Processor
{
    private $order;

    /**
     * @inheritdoc
     */
    public function run($data)
    {
        $value = $this->getValue();
        $this->order = isset($value['order']) ? $value['order'] : [];

        $this->customerEvents();
        $this->coreEvents();
    }

    private function customerEvents()
    {

    }

    private function coreEvents()
    {
        Observer::updateRebateReturnStatus($this->getValue());
    }
}