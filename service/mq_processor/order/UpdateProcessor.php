<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/9
 * Time: 11:57
 */

namespace service\mq_processor\order;


use framework\components\log\LogAbstract;
use framework\components\ToolsAbstract;
use service\mq_processor\Processor;

class UpdateProcessor extends Processor
{
    public function run($data)
    {
        $this->log('msg_id=' . $this->getMqMsgId() . ',version=' . $this->getVersion());
        $this->log($data);
    }

    protected function log($msg, $level = LogAbstract::LEVEL_INFO, $log2ES = false, $fileName = null)
    {
        parent::log($msg, $level, $log2ES, 'Order.UpdateProcessor.log');
    }
}