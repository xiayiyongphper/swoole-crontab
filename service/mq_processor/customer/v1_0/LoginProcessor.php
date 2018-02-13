<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/13
 * Time: 11:46
 */

namespace service\mq_processor\customer\v1_0;

use common\helpers\OfferTriggerHelper;
use framework\components\ToolsAbstract;
use service\mq_processor\Processor;

/**
 * 商家登录事件处理
 * @package service\models\customer
 */
class LoginProcessor extends Processor
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        ToolsAbstract::log('version=' . $this->getVersion(), 'CustomerLogin.log');
        return OfferTriggerHelper::triggeredByCustomerLogin($this->getValue(), $this->getMqMsgId());
    }
}