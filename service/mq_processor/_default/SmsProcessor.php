<?php
namespace service\mq_processor\_default;

use framework\components\ToolsAbstract;
use service\mq_processor\Processor;

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/2
 * Time: 14:06
 */
class SmsProcessor extends Processor
{
    public function run($data)
    {
        $this->log('SmsProcessor::run(),version=' . $this->getVersion());
    }
}