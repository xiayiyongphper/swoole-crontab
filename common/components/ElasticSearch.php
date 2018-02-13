<?php

namespace common\components;

use framework\components\ToolsAbstract;

/**
 * Author Jason Y.Wang
 * Class ElasticSearch
 * @package service\components\search
 */
class ElasticSearch
{
    /**
     * Function: search
     * Author: Jason Y. Wang
     * @param $city
     * @param $wholesalerId
     * @param boolean $self 是否设为自营商品
     */
    public static function updateProductSaleType($city, $wholesalerId, $self)
    {
        if (empty($city) || empty($wholesalerId)) {
            return;
        }

        $host = explode(',',ENV_ES_CLUSTER_HOSTS);

        $url = "'$host[0]'/products/{$city}/_update_by_query";
        if ($self) {
            $inline = 'ctx._source.sales_type=ctx._source.sales_type|8';  //设为自营商品
        } else {
            $inline = 'ctx._source.sales_type=ctx._source.sales_type＆～8';  //设为非自营商品
        }
        $params = [
            'script' => [
                'inline' => $inline,
                'lang' => 'groovy'
            ],
            'query' => [
                'term' => [
                    'wholesaler_id' => $wholesalerId
                ]
            ]
        ];

        $client = curl_init();
        curl_setopt($client, CURLOPT_URL, $url);
        curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($client, CURLOPT_POST, 1);
        curl_setopt($client, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($client);
        ToolsAbstract::log($result, 'updateProductsSalesTypeOnStoreUpdate.log');
        curl_close($client);
    }
}
