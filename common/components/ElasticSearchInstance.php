<?php

namespace common\components;

use common\helpers\ElasticSearchHelper;

/**
 * Author Jason Y.Wang
 * Class ElasticSearchInstance
 * @package service\components\search
 */
class ElasticSearchInstance
{
    /**
     * Function: search
     * @param $extra
     * @param $city
     * @return bool
     */
    public static function updateProduct($extra, $city)
    {
        if (empty($city) || empty($extra)) {
            return false;
        }

        $productList = $extra['productList'];
        if (empty($productList) || !is_array($productList)) {
            return false;
        }

        $productIds = [];
        foreach ($productList as $product) {
            if (isset($product['product_id']) && $product['product_id'] > 0) {
                array_push($productIds, $product['product_id']);
            }
        }

        if (empty($productIds)) {
            return false;
        }

        ElasticSearchHelper::updateProductToESByProductIds($city, $productIds);
        return true;
    }
}
