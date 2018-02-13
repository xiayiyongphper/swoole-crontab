<?php

namespace generator\models;

use framework\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "sales_flat_order_address".
 *
 * @property string $entity_id
 * @property integer $order_id
 * @property string $name
 * @property string $phone
 * @property string $address
 */
class SalesFlatOrderAddress extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        if (defined('ENV_DEBUG_MODE') && ENV_DEBUG_MODE) {
            return 'order_address';
        }
        return 'sales_flat_order_address';
    }

    /**
     * @return object|\yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('coreDb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entity_id' => 'Entity ID',
            'order_id' => 'Order ID',
            'name' => 'Customer Name',
            'address' => 'Address',
            'phone' => 'Phone',
        ];
    }
}
