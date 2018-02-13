<?php
/**
 * Created by PhpStorm.
 * User: Ryan Hong
 * Date: 2017/11/27
 * Time: 14:13
 */

namespace service\tasks\contractor;

use common\helpers\Tools;
use framework\db\readonly\models\core\ShelvesTemp;
use yii\db\Query;
use framework\components\Date;
use common\models\Products;
use yii\db\Expression;
use common\models\customer\CustomerShelvesProduct;
use common\models\merchant\SpecialProduct;
use service\tasks\TaskService;

/**
 * Class customerShelvesProduct
 * @package service\tasks\contractor
 */
class initCustomerShelvesProduct extends TaskService
{
    public function run($data){

//mysql_unbuffered_query()
        $round = 1;
        $row_num = 500;
        $max_id = 0;
        while(true){
            echo "round $round start".PHP_EOL;
            $rows = ShelvesTemp::find()->where(['>','entity_id',$max_id])
                ->limit($row_num);
//            Tools::log($rows->createCommand()->rawSql,'shelve.log');
            $rows = $rows->asArray()->all();
            if(empty($rows)) {
                echo "task finished".PHP_EOL;
                break;
            }

            foreach ($rows as $row){
//                Tools::log(json_encode($row),'shelve.log');
                $max_id = $row['entity_id'];

                $model = new Products($row['city']);
                $model = $model->findOne(['entity_id' => $row['product_id']]);
                if(empty($model)) continue;
                //特殊商品和秒杀商品不能加入货架
                if(SpecialProduct::isSecKillProduct($model->toArray(), 'entity_id')){
                    continue;
                }
                if(SpecialProduct::isSpecialProduct($row['product_id'])){
                    continue;
                }

                $shelves = CustomerShelvesProduct::find()
                    ->where([
                        'customer_id' => $row['customer_id'],
                        'lsin' => $model->lsin
                    ])->one();

                if(empty($shelves)){
                    $shelves = new CustomerShelvesProduct();
                    $shelves->customer_id = $row['customer_id'];
                    $shelves->lsin = $model->lsin ? : '';
                    $shelves->product_id = $row['product_id'];
                    $shelves->first_category_id = $model->first_category_id ? : 0;
                    $shelves->second_category_id = $model->second_category_id ? : 0;
                    $shelves->third_category_id = $model->third_category_id ? : 0;
                    $shelves->brand = $model->brand ? : '';
                    $shelves->buy_count = $row['buy_count'];
                    $shelves->latest_buy_time = $row['created_at'];
                    $shelves->latest_buy_num = $row['qty'];

                    $shelves->save();
                }else{
                    if(strtotime($shelves->latest_buy_time) < strtotime($row['created_at'])){

                        $shelves->latest_buy_time = $row['created_at'];
                        $shelves->latest_buy_num = $row['qty'];
                    }

                    $shelves->buy_count += $row['buy_count'];
                    $shelves->save();
                }
            }

            echo "round $round over".PHP_EOL;
            $round++;
        }

        return true;
    }
}