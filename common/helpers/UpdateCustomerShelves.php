<?php
/**
 * Created by PhpStorm.
 * User: Ryan Hong
 * Date: 2017/11/16
 * Time: 15:05
 */

namespace common\helpers;

use common\components\UserTools;
use common\models\customer\CustomerShelvesProduct;
use common\models\contractor\ContractorTaskHistory;
use framework\components\Date;
use framework\components\ToolsAbstract;
use framework\db\readonly\models\core\SalesFlatOrder;
use framework\mq\MQAbstract;
use common\models\merchant\SpecialProduct;
use common\models\Products;

/**
 * Class UpdateCustomerShelves
 * @package common\helpers
 */
class UpdateCustomerShelves
{
    public static function run($order,$extraData){
//        Tools::log($order,'shelve.log');
//        Tools::log($extraData,'shelve.log');
        if (empty($extraData['productList']) || !is_array($extraData['productList'])) {
            return false;
        }

        foreach ($extraData['productList'] as $key => $product){
            $productId = isset($product['product_id']) ? $product['product_id'] : null;
            $productId = $productId ? $productId : $key;    // 没有则取$key的值

            if(empty($productId)) return true;

            //特殊商品和秒杀商品不能加入货架
            if(SpecialProduct::isSecKillProduct($product, 'product_id')){
                return true;
            }
            if(SpecialProduct::isSpecialProduct($productId)){
                return true;
            }

            $productInfo = (new Products($order['city']))->findOne(['entity_id' => $productId]);
            if(empty($productInfo)){
                return true;
            }

            $shelves = CustomerShelvesProduct::find()
                ->where([
                    'customer_id' => $order['customer_id'],
                    'lsin' => $productInfo->lsin
                ])->one();
//            Tools::log($shelves->toArray(),'shelve.log');

            if(empty($shelves)){
                $shelves = new CustomerShelvesProduct();
                $shelves->customer_id = $order['customer_id'];
                $shelves->lsin = $productInfo->lsin;
                $shelves->product_id = $productId;
                $shelves->first_category_id = $product['first_category_id'];
                $shelves->second_category_id = $product['second_category_id'];
                $shelves->third_category_id = $product['third_category_id'];
                $shelves->brand = $product['brand'];
                $shelves->buy_count = 0;
            }

            $shelves->buy_count = intval($shelves->buy_count) + 1;
            $shelves->latest_buy_time = $order['created_at'];
            $shelves->latest_buy_num = $product['qty'];

            $shelves->save();
//            Tools::log($shelves->toArray(),'shelve.log');
        }
    }
}