<?php

namespace service\models\merchant;

use common\components\ElasticSearch;
use common\components\Events;
use common\components\events\ServiceEvent;
use common\components\Redis;
use common\helpers\MerchantProxy;
use common\helpers\reduceQty;
use common\helpers\Tools;
use common\models\core\SalesFlatOrder;
use common\models\core\SalesFlatOrderItem;
use common\models\GroupSubProducts;
use common\models\merchant\SpecialProduct;
use common\models\merchant\Store;
use common\models\Products;
use common\redis\Keys;
use Elasticsearch\ClientBuilder;
use framework\components\Date;
use framework\components\es\Console;
use framework\components\mq\Order;
use framework\components\ProxyAbstract;
use framework\components\ToolsAbstract;
use yii\db\Connection;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/29
 * Time: 17:23
 */
class Observer
{

    /**
     * 内部函数获取data
     * @param ServiceEvent|array $event
     * @return array
     */
    private static function getEventData($event)
    {
        if (!is_array($event)) {
            $event_data = $event->getEventData();
        } else {
            $event_data = $event;
        }
        Console::get()->log(print_r($event_data, true), null, [__METHOD__]);
        return $event_data;
    }

    /**
     * 更新商品缓存
     * @param ServiceEvent|array $event
     */
    public static function updateProductCache($event)
    {
        Console::get()->log(__METHOD__, null, [__METHOD__]);
        $event_data = self::getEventData($event);

        // 数据
        $product_list_by_city = $event_data['product_list_by_city'];

        $redis = ToolsAbstract::getRedis();
        foreach ($product_list_by_city as $city => $product_ids) {
            $redisKey = Redis::REDIS_KEY_PRODUCTS . "_" . $city;
            foreach ($product_ids as $product_id) {
                $redis->hDel($redisKey, $product_id);
            }
        }

    }

    /**
     * 更新店铺缓存
     * @param ServiceEvent|array $event
     */
    public static function updateMerchantStoreCache($event)
    {
        Console::get()->log(__METHOD__, null, [__METHOD__]);
        $event_data = self::getEventData($event);

        // 数据
        $wholesalerIds = $event_data;

        $redis = Tools::getRedis();
        $redisKey = Redis::REDIS_KEY_WHOLESALERS;
        foreach ($wholesalerIds as $wholesalerId) {
            $redis->hDel($redisKey, $wholesalerId);
        }

    }

    /*
     * 新订单,推送消息
     *
     *  array(
            'name' => 'order_new',
            'data'=>array(
                'wholesaler_id'=>1,
                'order_id'=>2,
            ),
        );

     */
    public static function orderNew($event)
    {
        Console::get()->log(__METHOD__, null, [__METHOD__]);
        $event_data = self::getEventData($event);

        // 数据
        $orderId = $event_data['entity_id'];
        $wholesalerId = $event_data['wholesaler_id'];

        Tools::notifyOrder($wholesalerId, $orderId, '您有一条新订单，请注意查看', 'new');
    }

    /*
     * 用户申请取消订单,发推送给商家app
     *
     *  array(
            'name' => 'order_new',
            'data'=>array(
                'wholesaler_id'=>1,
                'order_id'=>2,
            ),
        );
     */
    public static function orderApplyCancel($event)
    {
        Console::get()->log(__METHOD__, null, [__METHOD__]);
        $event_data = self::getEventData($event);

        // 数据
        $orderId = $event_data['entity_id'];
        $wholesalerId = $event_data['wholesaler_id'];

        Tools::notifyOrder($wholesalerId, $orderId, '有超市申请取消订单，请注意查看');

    }


    /*
     * 用户拒收订单,发推送给商家app
     *
     *  array(
            'name' => 'order_reject',
            'data'=>array(
                'wholesaler_id'=>1,
                'order_id'=>2,
            ),
        );
     */
    public static function orderReject($event)
    {
        Console::get()->log(__METHOD__, null, [__METHOD__]);
        $event_data = self::getEventData($event);

        // 数据
        $orderId = $event_data['entity_id'];
        $wholesalerId = $event_data['wholesaler_id'];

        Tools::notifyOrder($wholesalerId, $orderId, '您有一条订单已拒单');

    }


    /*
     * 用户签收订单,发推送给商家app
     *
     *  array(
            'name' => 'order_complete',
            'data'=>array(
                'wholesaler_id'=>1,
                'order_id'=>2,
            ),
        );
     */
    public static function orderComplete($event)
    {
        try {
            Console::get()->log(__METHOD__, null, [__METHOD__]);
            $event_data = self::getEventData($event);

            // 数据
            $orderId = $event_data['entity_id'];
            $wholesalerId = $event_data['wholesaler_id'];

            Tools::notifyOrder($wholesalerId, $orderId, '您有一条订单已确认收货');
        } catch (\Exception $e) {

        }
    }

    /**
     * Author Jason Y.Wang
     * 更新商品信息由定时任务处理
     * @param $data array|int
     * @return bool
     */
    public static function productUpdate($data)
    {
        $redis = ToolsAbstract::getRedis();
        $city = $data['product']['extra']['type'];
        $product_id = $data['product']['product']['product_id'];
        if (!$city || !$product_id) {
            Tools::log($data, 'productUpdate_error.log');
            return false;
        }
        $redis->rPush('updateEsList', serialize(['product_id' => $product_id, 'city' => $city, 'time' => time()]));
        return true;
    }

    /**
     * 减秒杀商品库存
     * @param array $extraData
     * @return boolean
     */
    public static function reduceSeckillProductStock($extraData)
    {
        if (empty($extraData['productList']) || !is_array($extraData['productList'])) {
            return false;
        }

        ToolsAbstract::log($extraData['productList'], 'reduceSeckillProductStock.log');

        /**
         * 2.9版本是$key为商品id，$product没有product_id的key
         * 3.0版本$key为订单商品表的item_id，$product有product_id的key
         * 所以先判断$product有没有product_id的key，没有则为2.9版本，$productId取$key的值。
         */
        foreach ($extraData['productList'] as $key => $product) {
            /* 处理秒杀商品 */
            $productId = isset($product['product_id']) ? $product['product_id'] : null;
            $productId = $productId ? $productId : $key;    // 没有则取$key的值
            if ($productId && !empty($product['type']) && SpecialProduct::isSpecialProduct($productId)) {
                $product['product_id'] = $productId;
                $num = (int)$product['qty'];
                if ($num > 0) {
                    //只有当是秒杀商品的时候才操作redis，扣减秒杀商品的库存
                    if (!empty($product['activity_id']) && SpecialProduct::isSecKillProduct($product, 'product_id')) {
                        /** 数据库减库存，只扣减秒杀商品的，特价活动商品在下单时就扣了 @see merchant.reduceQty() */
                        SpecialProduct::updateAllCounters(
                            ['qty' => -$num],
                            ['and',
                                ['entity_id' => $productId],
                                ['>=', 'qty', $num]
                            ]);

                        /* redis减库存 */
                        $key = sprintf('sk_total_%s_%s', $product['activity_id'], $productId);
                        ToolsAbstract::getRedis()->decrBy($key, $num);
                    }
                }
            }
        }
        return true;
    }

    /**
     * Author Jason Y.Wang
     * 在ES中删除商品信息
     * @param $data array|int
     * @return bool
     */
    public static function productDelete($data)
    {
        Tools::log($data, 'observer.log');
        $product_data = $data['product'];

        if (isset($product_data['product']['product_id']) && $product_data['product']['product_id'] > 0) {
            $city = $product_data['extra']['type'];
            if (!$city) {
                return false;
            }
            $hosts = \Yii::$app->params['es_cluster']['hosts'];
            $client = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
            $result = $client->delete([
                'id' => $product_data['product']['product_id'],
                'index' => 'products',
                'type' => $city,
            ]);
            Tools::log($result, 'observer.log');
        } else {
            Tools::log('product_id null', 'observer.log');
        }
        return true;
    }


    /**
     * 给orderId，整单退库存
     * array(
     * 'name' => 'order_decline',
     * 'data'=>array(
     * 'order_id'=>1,
     * ),
     * );
     * @param $data array|int
     */
    public static function orderRevertQty($data)
    {

        // 兼容自身直接传订单id过来调用
        if ($data && is_array($data) && isset($data['entity_id'])) {
            $orderId = $data['entity_id'];
        } elseif ($data && is_int($data)) {
            $orderId = $data;
        } else {
            Tools::log("传参错误！json_encode(data):" . json_encode($data), 'reduceOrderQty.log');
            return;
        }

        try {
            // 读取所有订单商品
            /** @var SalesFlatOrderItem[] $orderItems */
            Tools::log('order_id=' . print_r($orderId, 1), 'orderRevertQty.log');
            $orderItems = SalesFlatOrderItem::findAll(['order_id' => $orderId]);
            if ($orderItems) {
                $products = [];
                foreach ($orderItems as $item) {
                    $products[] = [
                        'product_id' => $item->product_id,
                        'wholesaler_id' => $item->wholesaler_id,
                        'num' => -$item->qty,
                        'type' => $item->product_type
                    ];
                }
                Tools::log($products, 'orderRevertQty.log');
                MerchantProxy::reduceQty($products, false); // 不修复套餐商品
            } else {
                Tools::log('no order items!', 'orderRevertQty.log');
            }
        } catch (\Exception $e) {
            Tools::log($e->getMessage(), 'reduceOrderQty.log');
        }
    }


    /**
     * 此处的事件触发点为：订单数据保存完成之后，并且确认有实质订单状态变更时会触发该事件。
     * @param ServiceEvent $event
     */
    public static function orderStateChanged(ServiceEvent $event)
    {
        Tools::log(__METHOD__, 'orderStateChanged.log');
        Tools::log($event, 'orderStateChanged.log');
        $data = $event->getEventData();
        /* @var $order SalesFlatOrder */
        $order = $data['order'];
        $oriState = $data['state'];
        $orderId = $order->getPrimaryKey();
        $events = [];
        switch ($order->status) {
            //新订单
            case SalesFlatOrder::STATUS_PROCESSING:
                break;
            //申请取消
            case SalesFlatOrder::STATUS_HOLDED:
                break;
            //商家接单
            case SalesFlatOrder::STATUS_PROCESSING_RECEIVE:
                break;
            // 订单取消
            case SalesFlatOrder::STATUS_CANCELED:
                // 之前状态是holed，说明是供应商同意取消
                if ($oriState == SalesFlatOrder::STATE_HOLDED) {
                    Order::publishAgreeCancelEvent($order->toArray());
                }
                //$order->setCompletedAt()->save();// 取消即到订单完结态
                break;
            // 订单完成
            case SalesFlatOrder::STATUS_COMPLETE:
                break;
            // 商家拒单
            case SalesFlatOrder::STATUS_CLOSED:
                // 自己响应，扣库存
                Order::publishOrderClosedEvent($order->toArray());
                //self::orderRevertQty($orderId);
                //$order->setCompletedAt()->save();// 到订单完结态
                break;
            // 超市拒收
            case SalesFlatOrder::STATUS_REJECTED_CLOSED:
                //$order->setCompletedAt()->save();// 到订单完结态
                break;
            // 超市签收,待评价
            case SalesFlatOrder::STATUS_PENDING_COMMENT:
                //$order->setCompletedAt()->save();// 到订单完结态
                break;
            case SalesFlatOrder::STATUS_WAITING_REFUND:
            case SalesFlatOrder::STATUS_REFUND:
            default:
        }
        $name = Events::EVENT_ES_ORDER_REPORT;
        // 订单信息变更，发送到core，上报到es
        $eventName = Events::getCoreEventName($name);
        $events[$eventName] = [
            'name' => $name,
            'data' => [
                'order_id' => $orderId,
            ]
        ];

        Tools::log($events, 'orderStateChanged.log');
        if (count($events) > 0) {
            foreach ($events as $eventName => $event) {
                ProxyAbstract::sendMessage($eventName, $event);
            }
        }
    }


    /**
     * @param SalesFlatOrder $order
     * @deprecated
     * @see \service\models\core\Observer::reduceBalanceDailyLimit()
     */
    static protected function reduceBalanceDailyLimit($order)
    {
        $redis = Tools::getRedis();
        $key = Keys::getBalanceDailyLimitKey($order->customer_id);

        $date = new Date();
        $ttl = strtotime($date->date('Y-m-d 00:00:00', strtotime("+1 day"))) - $date->timestamp();
        if (!$redis->exists($key)) {
            // 不存在则不管

        } else {
            $amount = $redis->get($key);
            if ($amount > $order->balance) {
                $redis->incrByFloat($key, -$order->balance);
                if ($redis->ttl($key) == -1) {
                    $redis->expire($key, $ttl);
                }
            } else {
                // 减到0
                $redis->del($key);
            }
        }
    }

    /**
     * 新订单更新套餐商品库存、子商品库存
     *
     * @param array $orderData
     * @param array $extraData
     * @return bool
     */
    public static function updateGroupProductStocksOnOrderNew($orderData, $extraData)
    {
        try {
            Tools::log('call updateGroupProductStocksOnOrderNew()!', 'group_product_debug.log');
            if (empty($extraData['productList']) || !is_array($extraData['productList']) || empty($orderData['city'])) {
                throw new \Exception('缺少必要参数');
            }

            $city = $orderData['city'];
            foreach ($extraData['productList'] as $product) {
                Tools::log('product_id=' . $product['product_id'], 'group_product_debug.log');
                if ($product['type'] & Products::TYPE_GROUP) {  // 套餐商品
                    self::updateGroupProductStockByGroupProductIdsEx($product['product_id'], $city);
                } elseif ($product['type'] & Products::TYPE_GROUP_SUB) {    // 子商品
                    self::updateGroupProductStockBySubProductId($product['product_id'], $city);
                } else {
                    Tools::log('not group product or sub product!', 'group_product_debug.log');
                }
            }
        } catch (\Exception $e) {
            Tools::log($e->getMessage(), 'group_product_debug.log');
        }
        return false;
    }

    /**
     * 当子商品更新时更新套餐商品
     *
     * @param array $params
     * @return bool
     */
    public static function updateGroupProductStockOnSubProductUpdate($params)
    {
        Tools::log('call updateGroupProductStockOnSubProductUpdate()', 'group_product_debug.log');
        Tools::log($params, 'group_product_debug.log');
        if (empty($params['product'])) {
            return false;
        }

        $data = $params['product'];
        $product = isset($data['product']) ? $data['product'] : [];
        if (empty($product['type']) || empty($product['product_id']) || empty($product['city'])) {
            return true;
        }

        if (!($product['type'] & Products::TYPE_GROUP)) {
            return true;
        }
        return self::updateGroupProductStockByGroupProductIdsEx($product['product_id'], $product['city']);
    }

    /**
     * 根据子商品的原商品ID更新套餐商品库存（根据子商品的库存算出实际库存的）
     *
     * @param int $productId 原商品ID
     * @param int $city
     * @throws \Exception
     * @return bool
     */
    private static function updateGroupProductStockBySubProductId($productId, $city)
    {
        Tools::log('call updateGroupProductStockBySubProductId()', 'group_product_debug.log');
        // 商品明细表保存的是原商品ID
        $groupIds = GroupSubProducts::find()->select('group_product_id')
            ->where(['ori_product_id' => $productId])->column();

        Tools::log($groupIds, 'group_product_debug.log');
        if (!$groupIds) {
            throw new \Exception(sprintf('该子商品(ID:%s)无关联的套餐商品', $productId));
        }

        return self::updateGroupProductStockByGroupProductIdsEx($groupIds, $city);
    }

    /**
     * 根据套餐商品IDs更新套餐商品库存（根据子商品的库存算出实际库存的）
     *
     * @param array $groupIds
     * @param int $city
     * @return bool
     * @throws \Exception
     */
    private static function updateGroupProductStockByGroupProductIdsEx($groupIds, $city)
    {
        Tools::log('call updateGroupProductStockByGroupProductIdsEx()', 'group_product_debug.log');
        if (!$groupIds || !$city) {
            Tools::log('invalid parameters', 'group_product_debug.log');
            return false;
        }

        $subProducts = GroupSubProducts::find()->select('entity_id,group_product_id,ori_product_id,sub_product_num')
            ->where(['group_product_id' => $groupIds])->all();

        Tools::log('===subProducts===', 'group_product_debug.log');
        if (!$subProducts) {
            throw new \Exception(sprintf('套餐商品(IDS:%s)没有子商品', implode(',', $groupIds)));
        }

        // 更新套餐商品库存，遍历按照套餐ID取最小值
        $map = [];
        /** @var GroupSubProducts $subProduct */
        $productModel = new Products($city);
        foreach ($subProducts as $subProduct) {
            Tools::log('===subProduct===ori_product_id=' . $subProduct->ori_product_id, 'group_product_debug.log');
            /** @var Products $productModel */
            $oriProductId = $subProduct->ori_product_id;
            $tbName = $productModel::tableName();
            $product = $productModel::getDb()->useMaster(function ($db) use ($tbName, $oriProductId) {
                /** @var Connection $db */
                $sql = 'SELECT entity_id,qty FROM ' . $tbName . ' WHERE entity_id=' . $oriProductId;
                return $db->createCommand($sql)->queryOne();
            });

            Tools::log('===$product===', 'group_product_debug.log');
            if ($product) {
                Tools::log('qty=' . $product['qty'], 'group_product_debug.log');
                // 取最小的
                $stock = (int)($product['qty'] / $subProduct->sub_product_num);
                if (isset($map[$subProduct->group_product_id])) {
                    $map[$subProduct->group_product_id] = min($map[$subProduct->group_product_id], $stock);
                } else {
                    $map[$subProduct->group_product_id] = $stock;
                }
            }
        }

        Tools::log('===map===', 'group_product_debug.log');
        Tools::log($map, 'group_product_debug.log');
        foreach ($map as $groupProductId => $value) {
            /** @var Products $groupProduct */
            $groupProduct = (new Products($city))::findOne(['entity_id' => $groupProductId]);
            Tools::log('====groupProduct===', 'group_product_debug.log');
            if ($groupProduct) {
                $groupProduct->qty = $value;
                Tools::log('qty=' . $value, 'group_product_debug.log');
                if (!$groupProduct->save()) {
                    Tools::log(sprintf(
                        '更新套餐商品(ID:%s)库存失败，原因：%s',
                        $groupProduct->entity_id,
                        print_r($groupProduct->getErrors(), 1)
                    ), 'group_product_debug.log');
                } else {
                    Tools::log('save success', 'group_product_debug.log');
                }
            }
        }
        return true;
    }

    /**
     * 套餐商品在取消/关闭/拒单时更新相应的库存
     *
     * @param array $orderData
     * @param array $extraData
     * @return bool
     */
    public static function updateGroupProductStocksOnOrderImcomplete($orderData, $extraData)
    {
        try {
            Tools::log($extraData, 'updateGroupProductStocksOnOrderImcomplete.log');
            if (empty($extraData['productList']) || !is_array($extraData['productList'])) {
                throw new \Exception('缺少productList参数');
            }

            foreach ($extraData['productList'] as $product) {
                $productId = $product['product_id'];
                /**
                 * 不是套餐子商品直接跳过，套餐商品的退库存的处理已经在退库存那里退了
                 * @see Observer::orderRevertQty()
                 */
                if (!($product['type'] & Products::TYPE_GROUP_SUB)) {
                    continue;
                }

                try {
                    if (!self::updateGroupProductStockBySubProductId($productId, $orderData['city'])) {
                        throw new \Exception(sprintf('更新失败，ID：%s', $productId));
                    }
                } catch (\Exception $e) {
                    Tools::log($e->getMessage(), 'updateGroupProductStocksOnOrderImcomplete.log');
                }
            }

        } catch (\Exception $e) {
            Tools::log($e->getMessage(), 'updateGroupProductStocksOnOrderImcomplete.log');
        }
        return false;
    }

    /**
     * 当子商品的原商品更新时，更新套餐商品库存
     *
     * @param array $params
     * @return bool
     */
    public static function updateGroupProductStocksOnProUpdate($params)
    {
        Tools::log($params, 'updateGroupProductStocksOnProUpdate.log');
        if (empty($params['product'])) {
            return false;
        }

        $data = $params['product'];
        $product = isset($data['product']) ? $data['product'] : [];
        $extra = isset($data['extra']) ? $data['extra'] : [];

        if (empty($product['type']) || empty($extra['is_stock_changed']) || empty($product['city'])) {
            return true;
        }

        if (!($product['type'] & Products::TYPE_GROUP_SUB)) {
            return true;
        }

        // 在计算套餐商品库存前先更新套餐商品的库存，因为套餐商品库存是根据子商品的库存算出实际库存的
        if (isset($product['qty']) && is_numeric($product['qty']) && $product['qty'] > 0) {
            GroupSubProducts::updateAll(
                ['qty' => $product['qty']],
                ['ori_product_id' => $product['product_id'], 'city' => $product['city']]
            );
        }
        return self::updateGroupProductStockBySubProductId($product['product_id'], $product['city']);
    }

    /**
     * 当店铺更新时，更新商品的sales_type
     *
     * @param array $params
     * @return bool
     */
    public static function updateProductsSalesTypeOnStoreUpdate($params)
    {
        Tools::log($params, 'updateProductsSalesTypeOnStoreUpdate.log');
        if (empty($params['store'])) {
            return false;
        }

        try {
            $store = $params['store'];
            ToolsAbstract::log('store!!');
            if (isset($store['origin_store_type']) && isset($store['store_type']) && !empty($store['wholesaler_id'])) {
                ToolsAbstract::log('in!!');
                /* 原来为自营或者现在为自营而且变更过则执行后面的操作 */
                $originStoreType = $store['origin_store_type'];
                $storeType = $store['store_type'];
                if ($originStoreType != $storeType &&
                    ($originStoreType == Store::SALES_TYPE_SELF || $storeType == Store::SALES_TYPE_SELF)
                ) {
                    if ($storeType == Store::SALES_TYPE_SELF) {   // 现在自营，刷商品为自营
                        ToolsAbstract::log('now is self', 'updateProductsSalesTypeOnStoreUpdate.log');
                        ElasticSearch::updateProductSaleType($store['city'], $store['wholesaler_id'], true);
                    } else {    // 现在不是自营，则去掉商品的自营属性
                        ToolsAbstract::log('now is not self', 'updateProductsSalesTypeOnStoreUpdate.log');
                        ElasticSearch::updateProductSaleType($store['city'], $store['wholesaler_id'], false);
                    }
                }
            }
        } catch (\Exception $e) {
            ToolsAbstract::log($e->__toString(), 'updateProductsSalesTypeOnStoreUpdate.log');
        }

        return true;
    }

    /**
     * @param SalesFlatOrder $order
     * @deprecated  合并后不用了
     * @see \service\models\core\Observer::reduceDailyPurchaseHistory()
     */
    public static function reduceDailyPurchaseHistory($order)
    {
        $key = Keys::getDailyPurchaseHistory($order->customer_id, $order->city);
        $redis = Tools::getRedis();
        $products = [];
        /** @var SalesFlatOrderItem $item */
        foreach ($order->getItemsCollection(false) as $item) {
            if (isset($products[$item->product_id])) {
                $products[$item->product_id] += (int)$item->qty;
            } else {
                $products[$item->product_id] = (int)$item->qty;
            }
        }
        $date = new Date();
        $ttl = strtotime($date->date('Y-m-d 00:00:00', strtotime("+1 day"))) - $date->timestamp();
        if ($redis->exists($key)) {
            foreach ($products as $productId => $qty) {
                $current = $redis->hIncrBy($key, $productId, -$qty);
                if ($current <= 0) {
                    $redis->hDel($key, $productId);
                }
            }
            if ($redis->ttl($key) == -1) {
                $redis->expire($key, $ttl);
            }
        }
    }
}
