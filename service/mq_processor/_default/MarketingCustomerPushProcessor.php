<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/6
 * Time: 14:54
 */

namespace service\mq_processor\_default;


use framework\components\log\LogAbstract;
use service\models\customer\Observer;
use service\mq_processor\Processor;

/**
 * Class MarketingCustomerPushProcessor
 * @package service\mq_processor\_default
 */
class MarketingCustomerPushProcessor extends Processor
{
    public function run($data)
    {
        /** TODO: wangyang说这是数据平台推送的消息，现在把入口停掉了，先不用迁移，暂时记录日志 */
        $this->log($data);
    }

    protected function log($msg, $level = LogAbstract::LEVEL_INFO, $log2ES = false, $fileName = null)
    {
        parent::log($msg, $level, $log2ES, 'MarketingCustomerPushProcessor.log');
    }
}