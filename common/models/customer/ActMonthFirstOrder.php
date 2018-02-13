<?php

namespace common\models\customer;

use Yii;

/**
 * This is the model class for table "act_month_first_order".
 *
 * @property integer $entity_id
 * @property integer $customer_id
 * @property integer $order_id
 * @property integer $order_created_at
 */
class ActMonthFirstOrder extends \framework\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_month_first_order';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('customerDb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'order_id', 'order_created_at'], 'required'],
            [['customer_id', 'order_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entity_id' => 'Entity ID',
            'customer_id' => '用户id',
            'order_id' => '月首单订单id',
            'order_created_at' => '订单时间',
        ];
    }
}
