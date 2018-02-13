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
use common\models\core\SalesFlatOrder;
use common\models\customer\BestSellingLsin7Days;
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
class BestSellingLsin extends TaskService
{
    public function run($data)
    {
//        ToolsAbstract::log(memory_get_usage(),'best_selling.log');
        //清空原数据
        BestSellingLsin7Days::updateAll(['order_num' => 0]);

        $end_time = ToolsAbstract::getDate()->date('Y-m-d 00:00:00');//今天凌晨
        $start_time = ToolsAbstract::getDate()->date('Y-m-d 00:00:00',strtotime($end_time) - 7 * 24 * 3600);//7天前

        //先查商品的订单数
        $order_count_data = SalesFlatOrderItem::find()
            ->alias('a')
            ->leftJoin(['b' => SalesFlatOrder::tableName()],'b.entity_id = a.order_id')
            ->select(['a.product_id','b.city',new Expression('COUNT(DISTINCT a.order_id) as order_num')])
            ->where(['>','a.created_at',$start_time])
            ->andWhere(['<','a.created_at',$end_time])
            //->andWhere(['product_type' => 0])
            ->groupBy(['a.product_id','b.city'])
            ->orderBy('order_num DESC');
//        ToolsAbstract::log($order_count_data->createCommand()->getRawSql(),'best_selling.log');
        $order_count_data = $order_count_data->asArray()
            ->all();
        //ToolsAbstract::log($order_count_data,'best_selling.log');

        if(!$order_count_data){
            return true;
        }

        foreach ($order_count_data as $row){
            $model = new Products($row['city']);
            $product = $model->find()
                ->select(['lsin','sort_weights','first_category_id','second_category_id','third_category_id','brand'])
                ->where(['entity_id' => $row['product_id']])
                ->asArray()->one();

            if(empty($product)) continue;

//            echo $product['lsin'].PHP_EOL;
            $record = BestSellingLsin7Days::findOne([
                'lsin' => $product['lsin']
            ]);

            if(!empty($record)){
                $record->order_num += $row['order_num'];
            }else{
                $record = new BestSellingLsin7Days();
                $record->lsin = $product['lsin'] ? : '';
                $record->order_num = $row['order_num'];
                $record->first_category_id = $product['first_category_id'] ? : 0;
                $record->second_category_id = $product['second_category_id'] ? : 0;
                $record->third_category_id = $product['third_category_id'] ? : 0;
                $record->brand = $product['brand'] ? : '';
            }

            $record->save();
            if(!empty($record->getErrors())){
                ToolsAbstract::logException(new \Exception(json_encode($record->getErrors())));
            }
        }

        return true;
    }
}