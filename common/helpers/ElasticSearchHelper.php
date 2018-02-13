<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 17-10-13
 * Time: 上午11:06
 */

namespace common\helpers;


use common\models\core\SalesFlatOrder;
use common\models\Products;
use framework\components\ToolsAbstract;

class ElasticSearchHelper
{

    const STEP = 5000;

    private $fields = [
        'p.entity_id as entity_id',
        'p.rebates as rebates',
        'p.name as name',
        'a.name as first_category_name',
        'b.name as second_category_name',
        'c.name as third_category_name',
        'd.sort as wholesaler_weight',
        'p.status',
        'p.commission',
        'p.label1',
        'p.type',
        'p.type2',
        'p.sales_type',
        'promotion_text',
        'lsin',
        'barcode',
        'wholesaler_id',
        'first_category_id',
        'second_category_id',
        'third_category_id',
        'brand',
        'package_num',
        'package_spe',
        'state',
        'sort_weights',
        'sold_qty',
        'price',
        'special_price',
        'rule_id',
        'special_from_date',
        'special_to_date',
        'promotion_text_from',
        'promotion_text_to',
        'real_sold_qty',
        'qty',
        'minimum_order',
        'gallery',
        'export',
        'origin',
        'package',
        'specification',
        'shelf_life',
        'description',
        'production_date',
        'restrict_daily',
        'subsidies_lelai',
        'subsidies_wholesaler',
        'promotion_title_from',
        'promotion_title_to',
        'promotion_title',
        'sales_attribute_name',
        'sales_attribute_value',
        'specification_num',
        'specification_unit',
        'fake_sold_qty',
        'special_rebates_from',
        'special_rebates_to',
        'special_rebates_lelai_from',
        'special_rebates_lelai_to',
        'special_rebates_lelai',
        'special_rebates',
        'is_calculate_lelai_rebates',
        'rebates_lelai',
        'shelf_from_date',
        'shelf_to_date',
    ];
    private $filterFields = ['special_from_date', 'special_to_date', 'promotion_text_from', 'promotion_text_to',
        'production_date', 'promotion_title_from', 'promotion_title_to', 'special_rebates_from', 'special_rebates_to',
        'special_rebates_lelai_from', 'special_rebates_lelai_to'];

    private $city;
    private $action;

    private $max_id;
    private $productModel;

    private function __construct($city, $action)
    {
        if (!$city || !in_array($action, ['index', 'update'])) {
            throw new \Exception('参数错误');
        }
        $this->city = $city;
        $this->action = $action;
        $productModel = new Products($city);
        $this->productModel = $productModel;
        $this->max_id = $productModel->find()->max('entity_id');
    }

    public static function indexProductToESByProductId($city, $product_id)
    {
        $helper = new ElasticSearchHelper($city, 'index');
        $helper->updateProductToESByProductId($product_id);
    }

    public static function updateProductToESByProductIds($city, $product_ids)
    {
        $helper = new ElasticSearchHelper($city, 'index');
        $helper->_updateProductToESByProductIds($product_ids);
    }

    public static function updateProductToESByCity($city, $action)
    {
        $helper = new ElasticSearchHelper($city, $action);
        $helper->updateProductToES();
    }

    private function _updateProductToESByProductIds($product_ids)
    {
        $client = \Yii::$app->get('elasticSearch');
        $formatProducts = $this->getProductByIds($product_ids);
        $params = [];

        foreach ($formatProducts as $product) {
            $formatProduct = $this->formatProduct($product);
            if (!$formatProduct) {
                continue;
            }
            if ($this->action == 'index') {
                $updateParam = $this->indexParams($formatProduct);
            } else {
                $updateParam = $this->updateParams($formatProduct);
            }
            $params['body'][] = $updateParam['action'];
            $params['body'][] = $updateParam['data'];
        }

        if (empty($params)) {
            return false;
        }
        $result = $client->bulk($params);
        ToolsAbstract::log($result['errors'], 'updateProductToESByCity.log');
        ToolsAbstract::log(count($result['items']), 'updateProductToESByCity.log');
        return true;
    }

    private function updateProductToESByProductId($product_id)
    {
        $client = \Yii::$app->get('elasticSearch');
        $formatProduct = $this->getProductById($product_id);
        $params = [];

        $updateParam = $this->updateParams($formatProduct);
        $params['body'][] = $updateParam['action'];
        $params['body'][] = $updateParam['data'];

        if (empty($params)) {
            return false;
        }

        $result = $client->bulk($params);
        ToolsAbstract::log($result['errors'], 'updateProductToESByCity.log');
        ToolsAbstract::log(count($result['items']), 'updateProductToESByCity.log');
        return true;
    }

    private function updateProductToES()
    {
        $client = \Yii::$app->get('elasticSearch');
        //批量更新
        for ($i = 0; $i <= $this->max_id; $i += self::STEP) {
            $params = [];
            $products = $this->getProductByIdRange($i);
            foreach ($products as $product) {
                $formatProduct = $this->formatProduct($product);
                if (!$formatProduct) {
                    continue;
                }
                if ($this->action == 'index') {
                    $updateParam = $this->indexParams($formatProduct);
                } else {
                    $updateParam = $this->updateParams($formatProduct);
                }
                $params['body'][] = $updateParam['action'];
                $params['body'][] = $updateParam['data'];
            }

            if (empty($params)) {
                continue;
            }
            $result = $client->bulk($params);
            ToolsAbstract::log($result['errors'], 'updateProductToESByCity.log');
        }
    }

    private function getProductByIdRange($start)
    {
        $products = $this->productModel->find()->alias('p')
            ->select($this->fields)
            ->leftJoin('lelai_slim_pms.catalog_category as a', 'a.entity_id = first_category_id')
            ->leftJoin('lelai_slim_pms.catalog_category as b', 'b.entity_id = second_category_id')
            ->leftJoin('lelai_slim_pms.catalog_category as c', 'c.entity_id = third_category_id')
            ->leftJoin('lelai_slim_merchant.le_merchant_store as d', 'd.entity_id = wholesaler_id')
            ->where(['between', 'p.entity_id', $start, $start + self::STEP])->asArray()->all();
        return $products;
    }

    private function getProductById($product_id)
    {
        $product = $this->productModel->find()->alias('p')
            ->select($this->fields)
            ->leftJoin('lelai_slim_pms.catalog_category as a', 'a.entity_id = first_category_id')
            ->leftJoin('lelai_slim_pms.catalog_category as b', 'b.entity_id = second_category_id')
            ->leftJoin('lelai_slim_pms.catalog_category as c', 'c.entity_id = third_category_id')
            ->leftJoin('lelai_slim_merchant.le_merchant_store as d', 'd.entity_id = wholesaler_id')
            ->where(['p.entity_id' => $product_id])->asArray()->one();
        return $product;
    }

    private function getProductByIds($product_ids)
    {
        $products = $this->productModel->find()->alias('p')
            ->select($this->fields)
            ->leftJoin('lelai_slim_pms.catalog_category as a', 'a.entity_id = first_category_id')
            ->leftJoin('lelai_slim_pms.catalog_category as b', 'b.entity_id = second_category_id')
            ->leftJoin('lelai_slim_pms.catalog_category as c', 'c.entity_id = third_category_id')
            ->leftJoin('lelai_slim_merchant.le_merchant_store as d', 'd.entity_id = wholesaler_id')
            ->where(['p.entity_id' => $product_ids])->asArray()->all();
        return $products;
    }

    private function formatProduct($product)
    {

        if (!is_array($product) || empty($product)) {
            return false;
        }

        //格式化时间格式字段
        foreach ($this->filterFields as $field) {
            if (isset($product[$field])) {
                if (strpos($product[$field], '0000-00-00') !== false) {
                    $product[$field] = null;
                }
            }
        }
        $product['entity_id'] = intval($product['entity_id']);
        $product['wholesaler_id'] = intval($product['wholesaler_id']);
        $product['first_category_id'] = intval($product['first_category_id']);
        $product['second_category_id'] = intval($product['second_category_id']);
        $product['third_category_id'] = intval($product['third_category_id']);
        $product['package_num'] = intval($product['package_num']);
        $product['status'] = intval($product['status']);
        $product['state'] = intval($product['state']);
        $product['package_num'] = intval($product['package_num']);
        $product['sort_weights'] = intval($product['sort_weights']);
        $product['sold_qty'] = intval($product['sold_qty']);
        $product['qty'] = intval($product['qty']);
        $product['restrict_daily'] = intval($product['restrict_daily']);
        $product['export'] = intval($product['export']);
        $product['real_sold_qty'] = intval($product['real_sold_qty']);
        $product['minimum_order'] = intval($product['minimum_order']);
        $product['fake_sold_qty'] = intval($product['fake_sold_qty']);
        $product['is_calculate_lelai_rebates'] = intval($product['is_calculate_lelai_rebates']);
        $product['sales_type'] = intval($product['sales_type']);

        $product['specification_num'] = floatval($product['specification_num']);
        $product['subsidies_wholesaler'] = floatval($product['subsidies_wholesaler']);
        $product['special_rebates_lelai'] = floatval($product['special_rebates_lelai']);
        $product['rebates_lelai'] = floatval($product['rebates_lelai']);
        $product['subsidies_lelai'] = floatval($product['subsidies_lelai']);

        $product['special_price'] = floatval($product['special_price']);
        $product['price'] = floatval($product['price']);
        $product['rule_id'] = intval($product['rule_id']);
        $product['wholesaler_weight'] = intval($product['wholesaler_weight']);

        //auto complete
        $product['brand_suggest'] = $product['brand'];
        $product['brand_agg'] = $product['brand'];
        $product['name_suggest'] = $product['name'];
        $product['specification_num_unit'] = $product['specification_num'] . $product['specification_unit'];
        $product['search_text'] = $product['promotion_text'] . $product['brand'] . $product['name'];

        return $product;
    }


    private function updateParams($formatProduct)
    {
        $action = [
            'update' => [
                '_index' => 'products',
                '_type' => $this->city,
                '_id' => $formatProduct['entity_id']
            ]
        ];
        $data = [
            'doc' => $formatProduct,
        ];
        return ['action' => $action, 'data' => $data];
    }

    private function indexParams($formatProduct)
    {
        $action = [
            'index' => [
                '_index' => 'products',
                '_type' => $this->city,
                '_id' => $formatProduct['entity_id']
            ]
        ];
        $data = $formatProduct;
        return ['action' => $action, 'data' => $data];
    }
}