<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午4:34
 */

namespace service\mq_processor\order;

use common\helpers\OfferTriggerHelper;
use common\helpers\SaasHelper;
use common\helpers\UpdateContractorTaskHistory;
use common\helpers\UpdateContractorStatistics;
use framework\components\ToolsAbstract;
use service\models\merchant\Observer;
use service\mq_processor\Processor;

/**
 * to do nothing
 * @package service\mq_processor\order
 */
class CreateProcessor extends Processor
{
    public function run($data)
    {
        //to do nothing
    }

}