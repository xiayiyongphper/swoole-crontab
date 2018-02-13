<?php

namespace console\controllers;

use common\helpers\ElasticSearchHelper;
use common\helpers\Tools;
use common\models\common\AvailableCity;
use common\models\Products;
use Elasticsearch\Client;
use yii\console\Controller;

/**
 * Site controller
 */
class EsController extends Controller
{
    public $index = 'products';

    protected $properties_mapping = [
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
            "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis",
        ],
        'shelf_to_date' => [
            'type' => 'date',
            "format" => "yyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis",
        ],
    ];

    public function actionBulkUpdate()
    {
        $startTime = microtime(true);
        $city_all = $this->getCity();
        /** @var AvailableCity $city */
        foreach ($city_all as $city) {
            try {
                $city_code = $city->city_code;
                Tools::log($city_code, 'updateProductToESByCity.log');
                ElasticSearchHelper::updateProductToESByCity($city_code,'update');
            } catch (\Exception $e) {
                echo $e->getMessage();
            } catch (\Error $e) {
                echo $e->getMessage();
            }
        }
        $endTime = microtime(true);
        echo $endTime - $startTime;
    }

    public function actionBulkImport()
    {
        $startTime = microtime(true);
        $city_all = $this->getCity();
        /** @var AvailableCity $city */
        foreach ($city_all as $city) {
            try {
                $city_code = $city->city_code;
                Tools::log($city_code, 'updateProductToESByCity.log');
                ElasticSearchHelper::updateProductToESByCity($city_code,'index');
            } catch (\Exception $e) {
                echo $e->getMessage();
            } catch (\Error $e) {
                echo $e->getMessage();
            }
        }
        $endTime = microtime(true);
        echo $endTime - $startTime;
    }

    /**
     * !important dangerous
     */
    public function actionDeleteIndex()
    {
        $client = \Yii::$app->get('elasticSearch');
        if ($client->indices()->exists(['index' => $this->index])) {
            $client->indices()->delete(['index' => $this->index]);
        }
    }

    public function actionCreateIndex()
    {
        /** @var Client $client */
        $client = \Yii::$app->get('elasticSearch');
        $properties_mapping = $this->properties_mapping;
        $properties_mapping['suggest'] = [
            'type' => 'completion'
        ];
        $client->indices()->create(
            [
                'index' => $this->index,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 3,
                        'number_of_replicas' => 1,
                    ],
                ]
            ]
        );
        $cities = $this->getCity();
        /** @var AvailableCity $city */
        foreach ($cities as $city) {
            $result = $client->indices()->putMapping([
                'index' => $this->index,
                'type' => $city->city_code,
                'body' => [
                    'properties' => $properties_mapping,
                    '_source' => [
                        'enabled' => true
                    ],
                ]
            ]);
            print_r($result);
        }
    }

    public function actionUpdateMapping()
    {
        $client = \Yii::$app->get('elasticSearch');

        $newFields = [
            'name_bak' => [
                'type' => 'string',
                "index" => "not_analyzed",
            ],
        ];
        $cities = $this->getCity();
        /** @var AvailableCity $city */
        foreach ($cities as $city) {
            $result = $client->indices()->putMapping([
                'index' => $this->index,
                'type' => $city->city_code,
                'body' => [
                    'properties' => $newFields,
                ]
            ]);
            print_r($result);
        }
    }

    private function getCity()
    {
        $city_all = AvailableCity::find()->all();
        return $city_all;
    }

}
