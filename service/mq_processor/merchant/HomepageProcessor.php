<?php

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/13
 * Time: 14:43
 */

namespace service\mq_processor\merchant;

use common\helpers\OfferTriggerHelper;
use framework\components\ToolsAbstract;
use framework\mq\MQAbstract;
use service\mq_processor\Processor;

/**
 * 用户进入首页事件
 * @see MQAbstract::MSG_MERCHANT_HOMEPAGE
 * @package service\mq_processor\merchant
 */
class HomepageProcessor extends Processor
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        ToolsAbstract::log('version=' . $this->getVersion(), 'HomepageProcessor.log');
        return OfferTriggerHelper::triggeredByEnterMerchantHomePage($this->getValue(), $this->getMqMsgId());
    }
}