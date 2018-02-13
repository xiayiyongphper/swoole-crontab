<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/8/29
 * Time: 14:28
 */

namespace service\tasks\core;


use framework\components\ToolsAbstract;
use common\models\core\SalesFlatOrderItem;
use common\models\BestSellingProduct;
use common\models\LeMerchantStore;
use common\models\Products;
use service\models\ProductHelper;
use service\tasks\TaskService;
use yii\base\Exception;
use yii\db\Expression;

/**
 * Class BestSellingProduct
 * @package service\tasks
 */
class BestSelling extends TaskService
{
    public function run($data)
    {
//        ToolsAbstract::log(memory_get_usage(),'best_selling.log');
        //清空原数据
        BestSellingProduct::deleteAll();

        $end_time = ToolsAbstract::getDate()->date('Y-m-d 00:00:00');//今天凌晨
        $start_time = ToolsAbstract::getDate()->date('Y-m-d 00:00:00',strtotime($end_time) - 72 * 3600);//72小时前

        //先查商品的订单数
        $order_count_data = SalesFlatOrderItem::find()
            ->select(['product_id','wholesaler_id',new Expression('COUNT(DISTINCT order_id) as order_num')])
            ->where(['>','created_at',$start_time])
            ->andWhere(['<','created_at',$end_time])
            //->andWhere(['product_type' => 0])
            ->groupBy(['product_id','wholesaler_id'])
            ->orderBy('order_num DESC');
//        ToolsAbstract::log($order_count_data->createCommand()->getRawSql(),'best_selling.log');
        $order_count_data = $order_count_data->asArray()
            ->all();
        //ToolsAbstract::log($order_count_data,'best_selling.log');

        if(!$order_count_data){
            return true;
        }

        $product_store_map = [];
        $wholesaler_ids = [];
        $order_num_map = [];
        foreach ($order_count_data as $row){
            if(!isset($product_store_map[$row['wholesaler_id']])){
                $product_store_map[$row['wholesaler_id']] = [];
            }
            $product_store_map[$row['wholesaler_id']] []= $row['product_id'];
            $wholesaler_ids []= $row['wholesaler_id'];
            $order_num_map[$row['product_id']] = $row['order_num'];
        }
        //ToolsAbstract::log($product_store_map,'best_selling.log');

        //所有店铺的城市
        $store_data = LeMerchantStore::find()
            ->select(['entity_id','city'])
            ->where(['entity_id' => $wholesaler_ids])
            ->asArray()->all();

        //product_id按城市分组
        $city_product_map = [];
        foreach ($store_data as $row){
            if(!isset($city_product_map[$row['city']])){
                $city_product_map[$row['city']] = [];
            }

            $city_product_map[$row['city']] = array_merge($city_product_map[$row['city']],$product_store_map[$row['entity_id']]);
        }

        //分城市查询商品信息,并根据lsin去重
        $lsin_map = [];
        foreach ($city_product_map as $city => $product_ids){
            if(empty($product_ids)) continue;

            $model = new Products($city);
            $products = $model->find()
                ->select(['entity_id','wholesaler_id','lsin','first_category_id'])
                ->where(['entity_id' => $product_ids])
                ->asArray()->all();

            if(empty($products)) continue;

            foreach ($products as $product){
                if(!isset($lsin_map[$product['lsin']]) || $lsin_map[$product['lsin']]['order_num'] < $order_num_map[$product['entity_id']]){
                    $product['order_num'] = $order_num_map[$product['entity_id']];
                    $product['city'] = $city;
//                    $product['product_id'] = $product['entity_id'];
//                    unset($product['entity_id']);
                    $lsin_map[$product['lsin']] = $product;
                }
            }
        }

        foreach ($lsin_map as $lsin => $product){
            $now = ToolsAbstract::getDate()->date();
            $model = new BestSellingProduct();
            $model->product_id = $product['entity_id'];
            $model->order_num = $product['order_num'];
            $model->wholesaler_id = $product['wholesaler_id'];
            $model->first_category_id = $product['first_category_id'];
            $model->city = $product['city'];
            $model->created_at = $now;
            $model->save();
        }

//        ToolsAbstract::log(memory_get_usage(),'best_selling.log');

//        $sql = "insert into lelai_slim_merchant.best_selling_product (product_id,wholesaler_id,first_category_id,city,order_num) select a.product_id,a.wholesaler_id,a.first_category_id,b.city,a.order_num from
//(select product_id,wholesaler_id,first_category_id,COUNT(DISTINCT order_id) as order_num from lelai_slim_core.sales_flat_order_item where created_at>'%s' and created_at < '%s'
//GROUP BY product_id,wholesaler_id,first_category_id ORDER BY order_num DESC) as a LEFT JOIN lelai_slim_merchant.le_merchant_store b on a.wholesaler_id=b.entity_id";
//        $sql = sprintf($sql,$start_time,$end_time);
//        ToolsAbstract::log($sql,'best_selling_product.log');
//
//        SalesFlatOrderItem::getDb()->createCommand($sql)->execute();

        return true;
    }
}