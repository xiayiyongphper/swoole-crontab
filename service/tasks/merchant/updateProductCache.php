<?php

namespace service\tasks\merchant;

use common\helpers\ElasticSearchHelper;
use framework\components\ToolsAbstract;
use service\tasks\TaskService;

class updateProductCache extends TaskService
{
    public function run($data)
    {
        $redis = ToolsAbstract::getRedis();

        $products = [];

        for ($i = 1; $i <= 100; $i++) {
            $data = $redis->lPop('updateEsList');
            if (!$data) {
                break;
            }
            $data = unserialize($data);
            $city = $data['city'];
            $product_id = $data['product_id'];
            $products[$city][] = $product_id;
            //删除redis缓存
            $redis->hDel('products_' . $city, $product_id);
        }

        foreach ($products as $city => $product_ids) {
            ElasticSearchHelper::updateProductToESByProductIds($city, $product_ids);
        }
        ToolsAbstract::log('商品更新成功','updateProductCache.log');
        return true;
    }
}