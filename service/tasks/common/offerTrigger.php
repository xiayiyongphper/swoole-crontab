<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/9/27
 * Time: 18:12
 */

namespace service\tasks\common;

use framework\components\log\LogAbstract;
use framework\components\ToolsAbstract;
use service\business\OfferTriggerBiz;
use service\entity\common\OfferTriggerEntity;
use service\tasks\TaskService;

/**
 * 优惠触发
 * @package service\tasks\common
 */
class offerTrigger extends TaskService
{
    /**
     * @inheritdoc
     */
    public function run($params)
    {
        /** @var OfferTriggerEntity $entity */
        $entity = $this->parseParams($params);
        $params = $entity->getTaskParams();
        try {
            $result = (new OfferTriggerBiz($params['offer_trigger_id'], $entity->getUserId()))->trigger();
            $this->log('result=' . print_r($result, 1), LogAbstract::LEVEL_INFO, false, 'offerTrigger.log');
            return ['success' => 1];
        } catch (\Exception $e) {
            ToolsAbstract::logException($e);
            $this->log($e->__toString(), LogAbstract::LEVEL_NOTICE, false, 'offerTrigger.log');
            throw $e;
        } catch (\Exception $e) {
            ToolsAbstract::logError($e);
            $this->log($e->__toString(), LogAbstract::LEVEL_NOTICE, false, 'offerTrigger.log');
            throw $e;
        }
    }
}