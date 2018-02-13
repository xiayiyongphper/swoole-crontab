<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午4:34
 */

namespace service\mq_processor\order;

use common\helpers\SaasHelper;
use framework\components\ToolsAbstract;
use service\models\customer\Observer;
use service\mq_processor\Processor;
use common\helpers\UpdateContractorTaskHistory;

class AgreeCancelProcessor extends Processor
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
        $this->contractorEvents();

        SaasHelper::notifySaas($data);

    }

    private function customerEvents()
    {
        try {
            // 退零钱
            Observer::return_balance($this->order);
            // 活动,每月首单标记
            Observer::act_monthFirstOrder_orderCancel($this->order);
            // push
            Observer::orderAgreeCancel($this->order);
        } catch (\Exception $e) {
            $this->log($e->__toString());
            ToolsAbstract::logException($e);
        } catch (\Error $error) {
            $this->log($error->__toString());
            ToolsAbstract::logError($error);
        }
    }

    private function coreEvents()
    {
        try {
            //退优惠券
            \service\models\core\Observer::returnCoupon($this->getValue());
            //退回每日限购的数量
            \service\models\core\Observer::revertDailyPurchaseHistory($this->getValue());
            //退回用户享受优惠的次数
            \service\models\core\Observer::revertCustomerRulesLimit($this->getValue());
            //取消订单回退当日可用额度
            \service\models\core\Observer::revertBalanceDailyLimit($this->getValue());
        } catch (\Exception $e) {
            $this->log($e->__toString());
            ToolsAbstract::logException($e);
        } catch (\Error $error) {
            $this->log($error->__toString());
            ToolsAbstract::logError($error);
        }
    }

    private function contractorEvents()
    {
        try {
            UpdateContractorTaskHistory::updateGmvStat($this->order, $this->getRoutingKey());
            UpdateContractorTaskHistory::updateOrderCustomerCount($this->order);
            UpdateContractorTaskHistory::updateOrderCount($this->order, $this->getRoutingKey());
        } catch (\Exception $e) {
            $this->log($e->__toString());
            ToolsAbstract::logException($e);
        } catch (\Error $error) {
            $this->log($error->__toString());
            ToolsAbstract::logError($error);
        }
    }
}