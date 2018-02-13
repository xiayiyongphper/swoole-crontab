<?php

namespace console\controllers;

//use service\tasks\generate;
use yii\console\Controller;
use service\tasks\contractor\initCustomerShelvesProduct;
use service\tasks\contractor\updateShelvesProduct;
use service\tasks\core\BestSelling;
use service\tasks\core\BestSellingLsin;
use yii\helpers\ArrayHelper;
/**
 * Site controller
 */
class TestController extends Controller
{
    /**
     * 用户ID
     * @var integer
     */
    protected $customerId = 35;

    /**
     * TOKEN
     * @var string
     */
    protected $authToken = 'KBovpuxTtPUbhq28';

    public function actionShelve()
    {
        $obj = new initCustomerShelvesProduct();
        $obj->run(1);
    }

    public function actionShelve2()
    {
        $obj = new updateShelvesProduct();
        $obj->run(1);
    }

    public function actionIndex()
    {
        $a = [
                    'product_id' => 9307,
                    'qty' => 3,
                    'type' => 8192,
                    'activity_id' => 0,
        ];
        print_r(ArrayHelper::getValue($a, 'product_id'));
    }

    public function actionBestselling()
    {
        $obj = new BestSelling();
        $obj->run(1);
    }

    public function actionBestSellingLsin()
    {
        $obj = new BestSellingLsin();
        $obj->run(1);
    }


}
