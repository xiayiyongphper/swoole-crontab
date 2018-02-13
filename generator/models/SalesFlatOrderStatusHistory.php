<?php

namespace generator\models;

use framework\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "sales_flat_order_status_history".
 *
 * @property string $entity_id
 * @property string $parent_id
 * @property integer $is_customer_notified
 * @property integer $is_visible_on_front
 * @property string $comment
 * @property string $status
 * @property string $created_at
 * @property mixed $operator
 */
class SalesFlatOrderStatusHistory extends ActiveRecord
{
    protected $_order;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        if (defined('ENV_DEBUG_MODE') && ENV_DEBUG_MODE) {
            return 'order_status_history';
        }
        return 'sales_flat_order_status_history';
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
            [['parent_id'], 'required'],
            [['parent_id', 'is_customer_notified', 'is_visible_on_front'], 'integer'],
            [['comment'], 'string'],
            [['created_at'], 'safe'],
            [['status'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entity_id' => 'Entity ID',
            'parent_id' => 'Parent ID',
            'is_customer_notified' => 'Is Customer Notified',
            'is_visible_on_front' => 'Is Visible On Front',
            'comment' => 'Comment',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }

    public function setOrder(SalesFlatOrder $order)
    {
        $this->_order = $order;
    }

}
