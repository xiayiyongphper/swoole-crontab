<?php

namespace service\business;

/**
 * Created by PhpStorm.
 * User: henry.zhu
 * Date: 2017/11/22
 * Time: 14:04
 */

use common\helpers\ElasticSearchHelper;
use common\models\common\AvailableCity;
use common\models\Products;
use Elasticsearch\ClientBuilder;
use framework\components\ToolsAbstract;
use service\exception\InvalidArgumentException;

/**
 * Class ProductSyncBiz
 * @package service\business
 */
class ProductSyncBiz
{
    private $client;
    public $index = 'products';

    protected function bulkImport($city_code)
    {
        if (empty($city_code)) {
            throw new InvalidArgumentException('City can not be null');
        }

        ElasticSearchHelper::updateProductToESByCity($city_code, 'index');
    }

    public function actionBulkImport()
    {
        $city_all = $this->getCity();
        /** @var AvailableCity $city */
        foreach ($city_all as $city) {
            try {
                $city_code = $city->city_code;
                $this->bulkImport($city_code);
            } catch (\Exception $e) {
                echo $e->getMessage();
            } catch (\Error $e) {
                echo $e->getMessage();
            }
        }
    }

    public function actionBulkUpdate()
    {
        $city_all = $this->getCity();
        /** @var AvailableCity $city */
        foreach ($city_all as $city) {
            try {
                $city_code = $city->city_code;
                $this->bulkUpdate($city_code);
            } catch (\Exception $e) {
                echo $e->getMessage();
            } catch (\Error $e) {
                echo $e->getMessage();
            }
        }
    }

    protected function getCity()
    {
        $city_all = AvailableCity::find()
            ->where(['city_code' => 421300])
            ->all();
        return $city_all;
    }

    protected function bulkUpdate($city_code)
    {
        if (empty($city_code)) {
            throw new InvalidArgumentException('City can not be null');
        }
        ElasticSearchHelper::updateProductToESByCity($city_code, 'update');
    }

    public function getProduct($product_id, $city)
    {
        $productModel = new Products($city);
        $product = $productModel->find()->alias('p')
            ->select(ProductSyncBiz::fields())
            ->leftJoin('lelai_slim_pms.catalog_category as a', 'a.entity_id = first_category_id')
            ->leftJoin('lelai_slim_pms.catalog_category as b', 'b.entity_id = second_category_id')
            ->leftJoin('lelai_slim_pms.catalog_category as c', 'c.entity_id = third_category_id')
            ->leftJoin('lelai_slim_merchant.le_merchant_store as d', 'd.entity_id = wholesaler_id')
            ->where(['p.entity_id' => $product_id])
            ->asArray()
            ->one();

        if (empty($product)) {
            throw new InvalidArgumentException('Product was not found');
        }

        foreach (ProductSyncBiz::filterFields() as $field) {
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

    /**
     * delete index
     */
    public function deleteIndex()
    {
        if ($this->exists(['index' => $this->index])) {
            $result = $this->getClient()->indices()->delete(['index' => $this->index]);
            $this->log($result);
        }
    }

    protected function exists($params)
    {
        return $this->getClient()->indices()->exists($params);
    }

    /**
     * create product index
     */
    public function createIndex()
    {
        $properties_mapping = ProductSyncBiz::properties();
        $properties_mapping['suggest'] = [
            'type' => 'completion'
        ];
        $params = [
            'index' => $this->index,
            'body' => [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 3,
                        'number_of_replicas' => 1,
                    ]
                ]
            ]
        ];
        //create index
        $result = $this->getClient()->indices()->create($params);
        $this->log($result);
    }

    /**
     * The PUT mapping API allows you to add a new type to an existing index,
     * or add new fields to an existing type:
     */
    public function putMapping()
    {
        $properties_mapping = ProductSyncBiz::properties();
        $properties_mapping['suggest'] = [
            'type' => 'completion'
        ];
        $cities = $this->getCity();
        /** @var AvailableCity $city */
        foreach ($cities as $city) {
            $result = $this->getClient()->indices()->putMapping([
                'index' => $this->index,
                'type' => $city->city_code,
                'body' => [
                    'properties' => $properties_mapping,
                ]
            ]);
            $this->log($result);
        }
    }

    /**
     * The PUT mapping API allows you to add a new type to an existing index,
     * or add new fields to an existing type:
     * @param [] $newProperties
     * <code>
     * $newProperties = [
     *       'name_bak' => [
     *        'type' => 'string',
     *        "index" => "not_analyzed",
     *       ],
     *   ];
     * </code>
     */
    public function updateMapping(array $newProperties)
    {
        $cities = $this->getCity();
        /** @var AvailableCity $city */
        foreach ($cities as $city) {
            $result = $this->getClient()->indices()->putMapping([
                'index' => $this->index,
                'type' => $city->city_code,
                'body' => [
                    'properties' => $newProperties,
                ]
            ]);
            $this->log($result);
        }
    }

    /**
     * all fields use in sql select
     * @return array
     */
    public static function fields()
    {
        return [
            'p.entity_id as entity_id',
            'p.rebates as rebates',
            'p.name',
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
            'br.sort as brand_weight',
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
    }

    public static function filterFields()
    {
        return [
            'special_from_date',
            'special_to_date',
            'promotion_text_from',
            'promotion_text_to',
            'production_date',
            'promotion_title_from',
            'promotion_title_to',
            'special_rebates_from',
            'special_rebates_to',
            'special_rebates_lelai_from',
            'special_rebates_lelai_to',
        ];
    }

    public static function properties()
    {
        return [
            'entity_id' => [
                'type' => 'integer',
            ],
            'lsin' => [
                'type' => 'string',
                "index" => "not_analyzed"
            ],
            'rebates' => [
                'type' => 'float',
            ],
            'commission' => [
                'type' => 'float',
            ],
            'brand_suggest' => [
                "type" => "string",
                "index" => "not_analyzed"
            ],
            'name_suggest' => [
                "type" => "string",
                "index" => "not_analyzed"
            ],
            'specification_num_unit' => [
                'type' => 'string',
                "analyzer" => "ik_max_word",
                "search_analyzer" => "ik_smart",
            ],
            'label1' => [
                'type' => 'integer',
            ],
            'first_category_name' => [
                'type' => 'string',
                "analyzer" => "ik_max_word",
                "search_analyzer" => "ik_smart",
            ],
            'second_category_name' => [
                'type' => 'string',
                "analyzer" => "ik_max_word",
                "search_analyzer" => "ik_smart",
            ],
            'third_category_name' => [
                'type' => 'string',
                "analyzer" => "ik_max_word",
                "search_analyzer" => "ik_smart",
            ],
            'status' => [
                'type' => 'integer',
            ],
            'sales_type' => [
                'type' => 'integer',
            ],
            'wholesaler_weight' => [
                'type' => 'integer',
            ],
            'brand_weight' => [
                'type' => 'integer',
            ],
            'name' => [
                'type' => 'string',
                "analyzer" => "ik_max_word",
                "search_analyzer" => "ik_smart",
            ],
            'promotion_text' => [
                'type' => 'string',
                "analyzer" => "ik_max_word",
                "search_analyzer" => "ik_smart",
            ],
            'barcode' => [
                'type' => 'string',
                "index" => "not_analyzed"
            ],
            'wholesaler_id' => [
                'type' => 'integer',
            ],
            'first_category_id' => [
                'type' => 'integer'
            ],
            'second_category_id' => [
                'type' => 'integer'
            ],
            'third_category_id' => [
                'type' => 'integer'
            ],
            'brand' => [
                'type' => 'string',
                "analyzer" => "ik_max_word",
                "search_analyzer" => "ik_smart",
            ],
            'brand_agg' => [
                'type' => 'string',
                "index" => "not_analyzed"
            ],
            'package_num' => [
                'type' => 'integer',
            ],
            'package_spe' => [
                'type' => 'string',
            ],
            'state' => [
                'type' => 'integer',
            ],
            'sort_weights' => [
                'type' => 'integer',
            ],
            'sold_qty' => [
                'type' => 'integer',
            ],
            'price' => [
                'type' => 'float',
            ],
            'special_price' => [
                'type' => 'float',
            ],
            'rule_id' => [
                'type' => 'integer',
            ],
            'special_from_date' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'special_to_date' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'promotion_text_from' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'promotion_text_to' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],

            'real_sold_qty' => [
                'type' => 'integer',
            ],
            'qty' => [
                'type' => 'integer',
            ],
            'minimum_order' => [
                'type' => 'integer',
            ],

            'gallery' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'export' => [
                'type' => 'integer',
            ],
            'origin' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'package' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'specification' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'shelf_life' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'description' => [
                'type' => 'string',
                "analyzer" => "ik_max_word",
                "search_analyzer" => "ik_smart",
            ],

            'production_date' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'restrict_daily' => [
                'type' => 'integer',
            ],
            'subsidies_lelai' => [
                'type' => 'float',
            ],
            'subsidies_wholesaler' => [
                'type' => 'float',
            ],

            'promotion_title_from' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'promotion_title_to' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'promotion_title' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'sales_attribute_name' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'sales_attribute_value' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'specification_num' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'specification_unit' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'type' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'type2' => [
                'type' => 'string',
                "index" => 'not_analyzed',
            ],
            'fake_sold_qty' => [
                'type' => 'integer',
            ],
            'special_rebates_from' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'special_rebates_to' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'special_rebates_lelai_from' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'special_rebates_lelai_to' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'special_rebates_lelai' => [
                'type' => 'float',
            ],
            'special_rebates' => [
                'type' => 'float',
            ],
            'is_calculate_lelai_rebates' => [
                'type' => 'integer',
            ],
            'rebates_lelai' => [
                'type' => 'float',
            ],
            'search_text' => [
                'type' => 'string',
                "analyzer" => "ik_max_word",
                "search_analyzer" => "ik_smart",
            ],
            'shelf_from_date' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
            'shelf_to_date' => [
                'type' => 'date',
                "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ],
        ];
    }

    /**
     * @return \Elasticsearch\Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $hosts = \Yii::$app->params['es_cluster']['hosts'];
            $this->client = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
        }
        return $this->client;
    }

    /**
     * dump log to target files
     * @param $msg
     */
    public function log($msg)
    {
        ToolsAbstract::log($msg, 'product-sync-biz.log');
    }
}