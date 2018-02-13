<?php

namespace generator\controllers;

use framework\components\ToolsAbstract;
use generator\components\OrderProcessor;
use generator\components\OrderWriter;
use generator\models\SalesFlatOrder;
use yii\console\Controller;

/**
 * Site controller
 */
class OrderController extends Controller
{
    public function actionIndex()
    {
//        throw new \Exception('Action Not Allowed');
        try {
            $writer = new OrderWriter();
            $writer->run();
        } catch (\Throwable $e) {
            ToolsAbstract::logError($e);
        }
    }

    public function actionOrderStatusProcess()
    {
        $processor = new OrderProcessor();
        $processor->run();
    }
}
