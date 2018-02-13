<?php

namespace service\tasks\test;

use framework\components\ToolsAbstract;
use service\tasks\TaskService;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/21
 * Time: 15:09
 */
class test1 extends TaskService
{
    public function run($data)
    {
        ToolsAbstract::log(__CLASS__ . '#' . print_r($data, 1), 'xxxx.log');
        return true;
    }
}