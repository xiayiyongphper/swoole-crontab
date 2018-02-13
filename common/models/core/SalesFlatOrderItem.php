<?php

namespace common\models\core;

use framework\db\ActiveRecord;

/**
 * This is the model class for table "sales_flat_order_item".
 *
 * @property string $item_id
 * @property string $order_id
 * @property integer $wholesaler_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $product_id
 * @property string $product_type
 * @property string $product_options
 * @property string $tags
 * @property string $weight
 * @property string $sku
 * @property string $name
 * @property string $brand
 * @property string $qty
 * @property string $price
 * @property string $original_price
 * @property float $row_total
 * @property string $image
 * @property string $barcode
 * @property string $specification
 * @property string $first_category_id
 * @property string $third_category_id
 * @property string $second_category_id
 * @property string $rebates
 * @property integer $is_calculate_lelai_rebates
 * @property float $rebates_calculate
 * @property float $commission
 * @property float $commission_percent
 * @property integer $receipt
 * @property float $subsidies_wholesaler
 * @property float $subsidies_lelai
 * @property float $rebates_lelai
 * @property float $rebates_calculate_lelai
 * @property string $origin
 * @property string $promotion_text
 * @property float $rule_apportion
 * @property float $rule_apportion_lelai
 * @property float $rule_apportion_wholesaler
 * @property string $buy_path 购买路径
 * @property integer $activity_id 活动id
 */
class SalesFlatOrderItem extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sales_flat_order_item';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('coreDb');
    }
}
