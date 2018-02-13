<?php

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/8/30
 * Time: 11:51
 */
namespace service\processes;

use framework\components\ToolsAbstract;
use framework\core\ProcessInterface;
use framework\core\SWServer;

/**
 * Class TestProcess1
 * @package service\processes
 */
class TestProcess1 implements ProcessInterface
{
    /**
     * @inheritdoc
     */
    public function run(SWServer $SWServer, \swoole_process $process)
    {
        while (true) {
            ToolsAbstract::log(sprintf('%s::%s,time=%s', TestProcess1::class, __FUNCTION__, ToolsAbstract::getDate()->date()));
            sleep(3);
        }
    }
}