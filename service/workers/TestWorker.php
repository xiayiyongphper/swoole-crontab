<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/8/29
 * Time: 10:24
 */

namespace service\workers;


use framework\components\ToolsAbstract;
use framework\core\BaseTaskServerWorker;
use framework\core\SWServer;
use framework\core\TaskServer;

/**
 * Class TestWorker
 * @package service\workers
 */
class TestWorker extends BaseTaskServerWorker
{
    public $testProp;

    /**
     * @inheritdoc
     */
    public function onTick(TaskServer $taskServer, $workerId, $timerId, $userParam = [])
    {
        ToolsAbstract::log($this->testProp . '##' . $timerId . '$$' . ToolsAbstract::getDate()->date());
        return parent::onTick($taskServer, $workerId, $timerId, $userParam);
    }

    /**
     * @param SWServer $server
     * @param int $workerId
     * @return bool
     */
    public function onWorkerStart(SWServer $server, $workerId)
    {
        ToolsAbstract::log(sprintf('from %s::%s', __CLASS__, __FUNCTION__));
        return parent::onWorkerStart($server, $workerId);
    }
}