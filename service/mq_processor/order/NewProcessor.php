<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午4:34
 */

namespace service\mq_processor\order;

use common\components\ElasticSearchInstance;
use common\helpers\OfferTriggerHelper;
use common\helpers\SaasHelper;
use common\helpers\Tools;
use common\helpers\UpdateContractorTaskHistory;
use common\helpers\UpdateContractorStatistics;
use framework\components\ToolsAbstract;
use service\models\merchant\Observer;
use service\mq_processor\Processor;
use yii\helpers\ArrayHelper;

/**
 * 下单事件处理
 * @package service\mq_processor\order
 */
class NewProcessor extends Processor
{
    private $order;
    private $extra;
    private $data;

    public function run($data)
    {
        $value = $this->getValue();
        $this->data = $data;
        $this->order = isset($value['order']) ? $value['order'] : [];
        $this->extra = isset($value['extra']) ? $value['extra'] : [];

        $this->customerEvents();
        $this->merchantEvents();
        $this->contractorEvents();
        $this->otherEvents();
    }

    private function customerEvents()
    {
        try {
            \service\models\customer\Observer::first_order_at($this->order);
            \service\models\customer\Observer::act_monthFirstOrder_orderNew($this->order);
            //改为同步消费零钱
            \service\models\customer\Observer::balance_consume($this->order);
            \service\models\customer\Observer::updateLastPlaceOrderAt($this->order);
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
            UpdateContractorStatistics::run($this->order);
        } catch (\Exception $e) {
            $this->log($e->__toString());
            ToolsAbstract::logException($e);
        } catch (\Error $error) {
            $this->log($error->__toString());
            ToolsAbstract::logError($error);
        }
    }

    private function merchantEvents()
    {
        try {
            Observer::orderNew($this->order);
            Observer::reduceSeckillProductStock($this->extra);
            Observer::updateGroupProductStocksOnOrderNew($this->order, $this->extra);
        } catch (\Exception $e) {
            $this->log($e->__toString());
            ToolsAbstract::logException($e);
        } catch (\Error $error) {
            $this->log($error->__toString());
            ToolsAbstract::logError($error);
        }
    }

    private function otherEvents()
    {
        try {
            OfferTriggerHelper::triggeredByOrderNew($this->getValue(), $this->getMqMsgId());
            SaasHelper::notifySaas($this->data);
            $city = isset($this->order['city']) ? $this->order['city'] : null;
            ElasticSearchInstance::updateProduct($this->extra, $city);
        } catch (\Exception $e) {
            $this->log($e->__toString());
            ToolsAbstract::logException($e);
        } catch (\Error $error) {
            $this->log($error->__toString());
            ToolsAbstract::logError($error);
        }
    }
}