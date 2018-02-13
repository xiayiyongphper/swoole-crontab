<?php

namespace service\tasks\test;

use framework\components\ToolsAbstract;
use service\entity\test\TestEntity;
use service\tasks\TaskService;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/21
 * Time: 15:09
 */
class test extends TaskService
{
    /**
     * @var TestEntity
     */
    private $entity;

    /**
     * @param mixed $data
     * @return bool
     */
    public function run($data)
    {
        $this->entity = $this->parseParams($data);
        ToolsAbstract::log(__CLASS__, 'xxxx.log');
        ToolsAbstract::log('task_id=' . $this->entity->getData('@task_id'), 'xxxx.log');
        ToolsAbstract::log('task_id=' . $this->entity->getTaskId(), 'xxxx.log');
        ToolsAbstract::log('params=' . print_r($this->entity->getTaskParams(), 1), 'xxxx.log');
        ToolsAbstract::log('origin_data=' . print_r($this->entity->toArray(), 1), 'xxxx.log');
        return true;
    }
}