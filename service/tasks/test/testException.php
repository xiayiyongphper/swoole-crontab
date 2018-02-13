<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/8/31
 * Time: 20:11
 */

namespace service\tasks\test;


use service\tasks\TaskService;

class testException extends TaskService
{
    /**
     * @inheritDoc
     */
    public function run($data)
    {
        throw new \Exception('this is exception task!');
    }
}