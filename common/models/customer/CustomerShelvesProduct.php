<?php
/**
 * Created by PhpStorm.
 * User: Ryan Hong
 * Date: 2017/11/27
 * Time: 14:02
 */

namespace common\models\customer;

use framework\db\ActiveRecord;

/**
 * Class CustomerShelvesProduct
 * @package common\models\customer
 * @property   integer $entity_id
 * @property   string $lsin
 * @property   integer $product_id
 * @property   integer $customer_id
 * @property   integer $first_category_id
 * @property   integer $second_category_id
 * @property   integer $third_category_id
 * @property   string $brand
 * @property   integer $buy_count
 * @property   string $latest_buy_time
 * @property   integer $latest_buy_num
 * @property integer $out_of_stock
 * @property float $buy_cycle_proportion
 */
class CustomerShelvesProduct extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_shelves_product';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('customerDb');
    }
}