<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/8/29
 * Time: 14:28
 */

namespace service\tasks\test;


use framework\components\ToolsAbstract;
use service\tasks\TaskService;

/**
 * Class helloworld
 * @package service\tasks
 */
class helloworld extends TaskService
{
    public function run($data)
    {
        ToolsAbstract::log($data, 'xxxx.log');
        return true;
    }
}