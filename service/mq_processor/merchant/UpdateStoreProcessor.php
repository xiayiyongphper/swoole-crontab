<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/10
 * Time: 17:30
 */

namespace service\mq_processor\merchant;


use framework\components\ToolsAbstract;
use framework\mq\MQAbstract;
use service\models\merchant\Observer;
use service\mq_processor\Processor;

/**
 * 更新店铺时
 * @see MQAbstract::MSG_MERCHANT_UPDATE_STORE
 * @package service\mq_processor\merchant
 */
class UpdateStoreProcessor extends Processor
{
    public function run($data)
    {
        ToolsAbstract::log($data);
        $this->merchantEvents();
    }

    private function merchantEvents()
    {
        try {
            Observer::updateProductsSalesTypeOnStoreUpdate($this->getValue());
        } catch (\Exception $e) {
            $this->log($e->__toString());
            ToolsAbstract::logException($e);
        } catch (\Error $error) {
            $this->log($error->__toString());
            ToolsAbstract::logError($error);
        }
    }
}