<?php

namespace console\controllers;

use common\components\OrderWriter;
use common\helpers\Tools;
use service\tasks\contractor\generateVisitPlan;
use yii\console\Controller;

/**
 * Site controller
 */
class IndexController extends Controller
{
    public function actionIndex()
    {
        $timeStamp = Tools::getDate()->timestamp();
        $yesterday = date('Y-m-d', strtotime('-1 day', $timeStamp));
        $today = Tools::getDate()->date('Y-m-d');
        print_r($yesterday);
        echo PHP_EOL;
        print_r($today);
    }

}
