<?php

namespace generator\models;

use framework\components\Date;
use framework\db\ActiveRecord;
use Yii;


/**
 * This is the model class for table "sales_flat_order".
 *
 * @property string $entity_id
 * @property string $state
 * @property string $status
 * @property integer $coupon_id
 * @property integer $wholesaler_id
 * @property string $wholesaler_name
 * @property string $phone
 * @property string $store_name
 * @property string $area_id
 * @property string $district
 * @property string $province
 * @property string $city
 * @property string $coupon_discount_amount
 * @property string $customer_id
 * @property float $grand_total
 * @property string $shipping_amount
 * @property string $discount_amount
 * @property string $subtotal
 * @property string $total_paid
 * @property string $total_qty_ordered
 * @property string $payment_method
 * @property integer $signed
 * @property integer $delivery_method
 * @property integer $customer_note_notify
 * @property integer $customer_group_id
 * @property integer $email_sent
 * @property string $total_due
 * @property string $increment_id
 * @property string $applied_rule_ids
 * @property string $order_currency_code
 * @property string $hold_before_state
 * @property string $hold_before_status
 * @property string $remote_ip
 * @property string $x_forwarded_for
 * @property string $customer_note
 * @property string $commission
 * @property string $balance
 * @property string $rebates
 * @property string $promotions
 * @property string $merchant_remarks
 * @property string $reserve_time
 * @property string $reserve_datetime
 * @property string $created_at
 * @property string $pay_time
 * @property string $updated_at
 * @property string $complete_at
 * @property integer $total_item_count
 * @property string $expire_time
 * @property \common\models\SalesOrderStatus $orderstatus
 * @property integer $receipt
 * @property integer $receipt_total
 * @property string $rebates_lelai
 * @property integer $source
 * @property string $source_version
 * @property string $device_id
 * @property integer $store_label1
 * @property integer $contractor_id
 * @property string $contractor
 * @property string $storekeeper
 * @property \common\models\SalesFlatOrderAddress $orderaddress
 * @property string $additional_info
 * @property integer $is_first_order
 * @property float $rebates_wholesaler
 * @property float $subsidies_lelai
 * @property float $subsidies_wholesaler
 * @property float $rule_apportion
 * @property float $rule_apportion_lelai
 * @property float $rule_apportion_wholesaler
 * @property integer $merchant_type_id
 * @property integer $customer_tag_id
 * @property integer $cancel_reason
 * @property integer $coupon_return_status 优惠券退回状态
 * @property integer $rebate_return_status 返现到账情况
 * @property integer $product_type 商品类型
 * @property integer $activity_id 秒杀活动id
 * @property float rule_apportion_order_act_lelai 整单订单级优惠活动的优惠金额部分，乐来部分
 * @property float rule_apportion_products_act_lelai 整单多品级优惠活动的优惠金额部分，乐来部分
 * @property float rule_apportion_order_coupon_lelai 整单订单级优惠券的优惠金额部分，乐来部分
 * @property float rule_apportion_products_coupon_lelai 整单多品级优惠券的优惠金额部分，乐来部分
 * @property integer $remind_count
 * @property string $remind_at
 */
class SalesFlatOrder extends ActiveRecord
{

    /**
     * 新订单
     */
    const STATE_NEW = 'new';
    /**
     * 退款
     */
    const STATE_REFUND = 'refund';
    /**
     * 处理中
     */
    const STATE_PROCESSING = 'processing';
    /**
     * 完成
     */
    const STATE_COMPLETE = 'complete';
    /**
     * 已关闭
     */
    const STATE_CLOSED = 'closed';
    /**
     * 已取消
     */
    const STATE_CANCELED = 'canceled';
    /**
     * 挂起
     */
    const STATE_HOLDED = 'holded';


    /**
     * 完成
     */
    const STATUS_COMPLETE = 'complete';
    /**
     * 关闭
     */
    const STATUS_CLOSED = 'closed';

    /**
     * 关闭
     */
    const STATUS_REJECTED_CLOSED = 'rejected_closed';

    /**
     * 已取消
     */
    const STATUS_CANCELED = 'canceled';
    /**
     * 挂起状态
     */
    const STATUS_HOLDED = 'holded';
    /**
     * 新订单
     */
    const STATUS_PENDING = 'pending';

    /**
     * 待商家确认
     */
    const STATUS_PROCESSING = 'processing';
    /**
     * 商家已接单
     */
    const STATUS_PROCESSING_RECEIVE = 'processing_receive';
    /**
     * 商家已发货
     */
    const STATUS_PROCESSING_SHIPPING = 'processing_shipping';

    /**
     * 退款成功
     */
    const STATUS_REFUND = 'refund';

    /**
     * 退款成功(拒单)
     */
    const STATUS_REJECTED_REFUND = 'rejected_refund';

    /**
     * 等待退款
     */
    const STATUS_WAITING_REFUND = 'waiting_refund';

    /**
     * 等待退款（拒单）
     */
    const STATUS_REJECTED_WAITING_REFUND = 'rejected_waiting_refund';

    /**
     * 待评论
     */
    const STATUS_PENDING_COMMENT = 'pending_comment';

    /**
     * 优惠券退回情况：已退回
     */
    const COUPON_RETURN_STATUS_RETURN = 1;

    /**
     * 返现到账情况：已到账
     */
    const REBATE_RETURN_STATUS_RETURN = 1;

    //超市tag,普通
    const CUSTOMER_TAG_ID_NORMAL = 1;

    /**
     * 退款失败
     * @deprecated
     */
    const STATUS_REFUND_FAILURE = 'refund_failure';
    public $receive_time;
    protected $_quote;
    protected $_statusHistory = [];

    /**
     * @var SalesFlatOrderAddress
     */
    protected $_address;
    protected $_items = [];
    protected $_orderStateChanged = false;
    protected $_oriState;
    protected $_oriStatus;
    protected $_traceId;

    const RECEIPT_NO = 0;
    const RECEIPT_ALL = 1;
    const RECEIPT_PARTIAL = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        if (defined('ENV_DEBUG_MODE') && ENV_DEBUG_MODE) {
            return 'order';
        }
        return 'sales_flat_order';
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
            [['wholesaler_id', 'customer_id', 'total_item_count'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entity_id' => 'Entity Id',
            'state' => 'State',
            'status' => 'Status',
            'coupon_code' => 'Coupon Code',
            'wholesaler_id' => 'Store Id',
            'customer_id' => 'Customer Id',
            'grand_total' => 'Grand Total',
            'shipping_amount' => 'Shipping Amount',
            'discount_amount' => 'Discount Amount',
            'subtotal' => 'Subtotal',
            'total_paid' => 'Total Paid',
            'total_qty_ordered' => 'Total Qty Ordered',
            'payment_method' => 'Payment Method',
            'delivery_method' => 'Delivery Method',
            'customer_note_notify' => 'Customer Note Notify',
            'customer_group_id' => 'Customer Group Id',
            'email_sent' => 'Email Sent',
            'total_due' => 'Total Due',
            'increment_id' => 'Increment Id',
            'applied_rule_ids' => 'Applied Rule Ids',
            'order_currency_code' => 'Order Currency Code',
            'hold_before_state' => 'Hold Before State',
            'hold_before_status' => 'Hold Before Status',
            'remote_ip' => 'Remote Ip',
            'x_forwarded_for' => 'X Forwarded For',
            'customer_note' => 'Customer Note',
            'balance' => '钱包余额支付金额',
            'rebates' => '整单返现金额（返到钱包余额）',
            'merchant_remarks' => '商家备注',
            'reserve_time' => 'Reserve Time',
            'reserve_datetime' => 'Reserve Datetime',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'total_item_count' => 'Total Item Count',
        ];
    }

    public function updateStatus($arr, $id)
    {
        if ($arr['comment']) {
            $comment = $arr['comment'];
        }
        unset($arr['comment']);
        $update_res = SalesFlatOrder::updateAll($arr, ['entity_id' => $id]);
        if ($update_res) {
            $orderinfo = SalesFlatOrder::findOne($id);
            $status_history = new SalesFlatOrderStatusHistory();
            $status_history->status = $orderinfo->status;
            $status_history->parent_id = $id;
            $status_history->is_customer_notified = 2;
            $status_history->created_at = date('Y-m-d H:i:s');
            $status_history->comment = $comment;
            if ($status_history->save()) {
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    public static function getGeneralSelectColumns()
    {
        return [
            'entity_id',
            'increment_id',
            'wholesaler_id',
            'state',
            'status',
            'payment_method',
            'phone',
            'customer_note',
            'shipping_amount',
            'total_qty_ordered',
            'subtotal',
            'grand_total',
            'pay_time',
            'complete_at',
            'created_at'
        ];
    }

    public function setQuote($quote)
    {
        $this->_quote = $quote;
    }

    /**
     * Order state setter.
     * If status is specified, will add order status history with specified comment
     * the setData() cannot be overriden because of compatibility issues with resource model
     *
     * @param string $state
     * @param string|bool $status
     * @param string $comment
     * @param mixed $timestamp
     * @return $this
     */
    public function setState($state, $status, $comment = '', $timestamp)
    {
        return $this->_setState($state, $status, $comment, $timestamp);
    }

    /**
     * Order state protected setter.
     * By default allows to set any state. Can also update status to default or specified value
     * Сomplete and closed states are encapsulated intentionally, see the _checkState()
     *
     * @param string $state
     * @param string $status
     * @param string $comment
     * @param mixed $timestamp
     * @return $this
     * @throws \Exception
     * @internal param $shouldProtectState
     */
    protected function _setState($state, $status, $comment = '', $timestamp)
    {
        $this->state = $state;
        $this->status = $status;
        $this->addStatusHistoryComment($comment, $status, $timestamp); // no sense to set $status again
        return $this;
    }

    /**
     * Add a comment to order
     * Different or default status may be specified
     *
     * @param string $comment
     * @param string $status
     * @param string $timestamp
     * @return SalesFlatOrderStatusHistory
     */
    public function addStatusHistoryComment($comment, $status, $timestamp)
    {
        $history = new SalesFlatOrderStatusHistory();
        $history->status = $status;
        $history->comment = $comment;
        $history->is_customer_notified = 0;
        $history->operator = 0;
        $history->created_at = $timestamp;
        $this->addStatusHistory($history);
        return $history;
    }

    /**
     * Set the order status history object and the order object to each other
     * Adds the object to the status history collection, which is automatically saved when the order is saved.
     * See the entity_id attribute backend model.
     * Or the history record can be saved standalone after this.
     *
     * @param SalesFlatOrderStatusHistory $history
     * @return $this
     */
    public function addStatusHistory(SalesFlatOrderStatusHistory $history)
    {
        $history->setOrder($this);
        $this->status = $history->status;
        if (!$history->getPrimaryKey()) {
            $this->_statusHistory[] = $history;
        }
        return $this;
    }

    /**
     * 添加地址
     * @param SalesFlatOrderAddress $address
     * @return $this
     */
    public function setAddress(SalesFlatOrderAddress $address)
    {
        $this->_address = $address;
        return $this;
    }

    /**
     * 添加订单商品
     * @param SalesFlatOrderItem $item
     * @return $this
     */
    public function addItem(SalesFlatOrderItem $item)
    {
        if (!$item->getPrimaryKey()) {
            $this->_items[] = $item;
        }
        return $this;
    }

    /**
     * 订单保存之后的处理
     * @param bool $insert
     * @param array $changedAttributes
     * @return $this
     */
    public function afterSave($insert, $changedAttributes)
    {
        /**
         * 订单地址数据
         */
        if (null !== $this->_address) {
            $this->_address->order_id = $this->getPrimaryKey();
            $this->_address->save();
        }

        /**
         * 订单商品数据
         */
        if (null !== $this->_items && count($this->_items) > 0) {
            /** @var SalesFlatOrderItem $item */
            foreach ($this->_items as $item) {
                $item->order_id = $this->getPrimaryKey();
                $item->save();
            }
        }

        /**
         * 订单交易数据
         */
        /*if (null !== $this->_transactions) {
            //$this->_transactions->save();
        }*/
        /**
         * 订单状态历史
         */
        if (null !== $this->_statusHistory && count($this->_statusHistory) > 0) {
            /** @var SalesFlatOrderStatusHistory $statusHistory */
            foreach ($this->_statusHistory as $statusHistory) {
                $statusHistory->parent_id = $this->getPrimaryKey();
                $statusHistory->save();
            }
        }

        /*
         * 无需触发订单事件，目前的订单不涉及相关的内容
         * 订单保存之后触发事件，并且要求订单状态发生实际改变时才触发事件。
         */

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTraceId()
    {
        return $this->_traceId;
    }

    /**
     * @param mixed $traceId
     */
    public function setTraceId($traceId)
    {
        $this->_traceId = $traceId;
    }


    public function setCompletedAt()
    {
        $date = new Date();
        $time = $date->gmtDate();
        $this->complete_at = $time;
        return $this;
    }


    /**
     * @param bool|true $asArray
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getItemsCollection($asArray = true)
    {
        return SalesFlatOrderItem::find()->where(['order_id' => $this->getPrimaryKey()])->asArray($asArray)->all();
    }

    /**
     * @param $where ['product_id'=>[1,2,3,4],'barcode'=>[1,2,3,4]]
     * @param bool|true $asArray
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function _getItemsCollection($where, $asArray = true)
    {
        $query = SalesFlatOrderItem::find()->where(['order_id' => $this->getPrimaryKey()]);
        if (is_array($where) && count($where)) {
            $query->andWhere($where);
        }
        return $query->asArray($asArray)->all();
    }


    /**
     * get items property
     */
    public function getItems()
    {
        return $this->_items;
    }

}
