<?php

namespace service\tasks\merchant;

use common\helpers\ElasticSearchHelper;
use common\helpers\Tools;
use common\models\common\AvailableCity;
use service\tasks\TaskService;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/21
 * Time: 15:09
 */
class updateEsProduct extends TaskService
{
    public function run($data = null)
    {
        $city_all = $this->getCity();
        /** @var AvailableCity $city */
        foreach ($city_all as $city) {
            try {
                $city_code = $city->city_code;
                Tools::log($city_code, 'updateProductToESByCity.log');
                ElasticSearchHelper::updateProductToESByCity($city_code, 'update');
            } catch (\Exception $e) {
                echo $e->getMessage();
            } catch (\Error $e) {
                echo $e->getMessage();
            }
        }
    }

    private function getCity()
    {
        $city_all = AvailableCity::find()->all();
        return $city_all;
    }

}