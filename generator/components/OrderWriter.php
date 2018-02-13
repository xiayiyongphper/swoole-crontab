<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-26
 * Time: 上午10:30
 */

namespace generator\components;


use framework\components\Date;
use framework\components\ToolsAbstract;
use generator\models\LeCustomers;
use generator\models\LeMerchantStore;
use generator\models\Orders;
use generator\models\Product;
use generator\models\Products;
use generator\models\SalesFlatOrder;
use generator\models\SalesFlatOrderItem;
use yii\helpers\ArrayHelper;

class OrderWriter
{
    protected $subsidies = [
        '2017-01' => 0.003,
        '2017-02' => 0.004,
        '2017-03' => 0.005,
    ];
    protected $customers;

    public function run()
    {
        $time1 = microtime(true);
        //毛利商品
        $cities = LeMerchantStore::find()
            ->addSelect('city')
//            ->where(['=', 'city', 441800])
            ->andWhere(['store_type' => 7])
            ->groupBy('city')->column();
        foreach ($cities as $city) {
            $this->log("+++++++++++++++++++++++++++++++++++++++++++++++++++++++++ $city BOF++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");
            $this->customers = null;
            $this->processCity($city);
            $this->log("+++++++++++++++++++++++++++++++++++++++++++++++++++++++++ $city EOF++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");
//            break;
        }
        $time2 = microtime(true);
        echo 'Elapsed:' . round($time2 - $time1) . 'sec.' . PHP_EOL;
    }

    /**处理每个城市的额数据
     * @param $city
     */
    protected function processCity($city)
    {
        //按城市用户
        $query = LeCustomers::find()
            ->where(['city' => $city])
            ->andWhere(['tag_id' => 7]);

        $this->customers = $query->all();
        $this->log($query->createCommand()->getRawSql());
        $this->log('当前城市的用户数为：' . count($this->customers));
        $sql = 'SELECT * FROM(SELECT LEFT(`day`, 7) as ym FROM `orders`) as b GROUP BY b . ym';
        $data = Orders::getDb()->createCommand($sql)->queryAll();
        $months = ArrayHelper::getColumn($data, 'ym');
        $this->log($months);
//        $months = ['2016-12'];
        foreach ($months as $month) {
            $this->log("************************************* $city $month BOF*************************************");
            $this->processMonthCity($city, $month);
            $this->log("************************************* $city $month EOF*************************************");
//            break;
        }
    }

    protected function processMonthCity($city, $month)
    {
        //首先将商品分为有毛利商品和无毛利商品
        //毛利商品
        $wholesalerIds = LeMerchantStore::find()
            ->addSelect('entity_id')
            ->where(['store_type' => 7])
            ->andWhere(['city' => $city])
            ->column();
        $this->log($wholesalerIds);
        //由于所有商家的商品都是一样的，这里任意取一个供货商即可
        $wholesalerId = current($wholesalerIds);
        //商品维度上面，考虑直接忽略商家的信息，在生成订单时，才考虑进行供货商分配。
        $allProductCollection = (new Products($city))::find()
            ->andWhere(['wholesaler_id' => $wholesalerId])
            ->asArray()
            ->all();
//        $this->log($allProductCollection);

        $profitProductCollection = Product::find()
            ->where(['<>', 'gross_profit', 0])
            ->andWhere(['city' => $city])
            ->andWhere(['month' => $month])
            ->andWhere(['wholesaler_id' => $wholesalerId])
            ->asArray()
            ->all();

//        $this->log('$profitProducts:' . count($profitProductCollection));
        $profitProducts = [];
        $nonProfitProducts = [];
        foreach ($allProductCollection as $productItem) {
            $isProfitProduct = false;
            foreach ($profitProductCollection as $profitProductItem) {
                if ($productItem['entity_id'] == $profitProductItem['product_id']) {
                    $isProfitProduct = true;
                    $product = new Product();
                    $product->entity_id = $profitProductItem['product_id'];
                    $product->city = $profitProductItem['city'];
                    $product->city_name = $profitProductItem['city_name'];
                    $product->gmv = $profitProductItem['gmv'];
                    $product->price = $profitProductItem['price'];
                    $product->qty = $profitProductItem['qty'];
                    $product->gross_profit = $profitProductItem['gross_profit'];
                    $product->barcode = $profitProductItem['barcode'];
                    $product->month = $profitProductItem['month'];
                    $product->product_id = $profitProductItem['product_id'];
                    $product->wholesaler_id = $profitProductItem['wholesaler_id'];
                    $product->single_gross_profit = $profitProductItem['single_gross_profit'];
                    $product->gross_profit_rate = $profitProductItem['gross_profit_rate'];
                    $product->name = $productItem['name'];
                    $product->first_category_id = $productItem['first_category_id'];
                    $product->second_category_id = $productItem['second_category_id'];
                    $product->third_category_id = $productItem['third_category_id'];
                    $product->brand = $productItem['brand'];
                    $gallery = explode(';', $productItem['gallery']);
                    $product->image = current($gallery);
                    $product->specification = $productItem['specification'];
                    $product->origin = $productItem['origin'];
                    $product->production_date = $productItem['production_date'];
                    $profitProducts[] = $product;
                }
            }
            if (!$isProfitProduct) {
                $product = new Product();
                $product->entity_id = $productItem['entity_id'];
                $product->city = $city;
                $product->city_name = $city;
                $product->gmv = 0;
                $product->price = $productItem['price'];
                $product->qty = $productItem['qty'];
                $product->gross_profit = 0;
                $product->barcode = $productItem['barcode'];
                $product->month = '1999-01';
                $product->product_id = $productItem['entity_id'];
                $product->wholesaler_id = $productItem['wholesaler_id'];
                $product->single_gross_profit = 0;
                $product->gross_profit_rate = 0;
                $product->name = $productItem['name'];
                $product->first_category_id = $productItem['first_category_id'];
                $product->second_category_id = $productItem['second_category_id'];
                $product->third_category_id = $productItem['third_category_id'];
                $product->brand = $productItem['brand'];
                $gallery = explode(';', $productItem['gallery']);
                $product->image = current($gallery);
                $product->specification = $productItem['specification'];
                $product->origin = $productItem['origin'];
                $product->production_date = $productItem['production_date'];
                $nonProfitProducts[] = $product;
            }

        }
//        $this->log('$nonProfitProducts');
//        $this->log($nonProfitProducts);
//        $this->log('$profitProducts');
//        $this->log($profitProducts);
//        return;
//        $this->log('$nonProfitProducts:' . count($nonProfitProducts));
        $orders = Orders::find()
            ->where(['city' => $city])
            ->andWhere(['like', 'day', $month])
            ->all();
        if (count($orders) == 0) {
            $this->log(sprintf('城市:%s在%s月份，没有订单！', $city, $month));
            return false;
        }
        $this->log('$orders:' . count($orders));
        $matcher = new OrderMatcher();
        $matcher->setProfitProducts($profitProducts);
        $matcher->setNonProfitProducts($nonProfitProducts);
        $matcher->setOrders($orders);
        $matcher->setCity($city);
        $matcher->setMonth($month);
        $matcher->match();
        $collection = $matcher->getMatchedCollection();
        $this->processMatchedCollection($collection);
    }

    protected function processMatchedCollection($collection)
    {
        foreach ($collection as $item) {
            $this->processMatchedItem($item);
        }
    }

    protected function processMatchedItem($item)
    {
//        $this->log($item);
        /** @var Orders $orderData */
        $orderData = $item['order'];
        $city = $orderData->city;
        $orderMonth = substr($orderData->day, 0, 7);//订单所在的月份
        /** @var LeMerchantStore $wholesaler */
        $wholesaler = $this->getWholesaler($city);
        $customer = $this->getCustomer($orderData->day);

//        $this->log($wholesaler);
        $timestamp = $this->getOrderCreatedAtTimestamp($orderData->day);
        $order = new SalesFlatOrder();
        $order->increment_id = $this->getIncrementIdByDay($timestamp);
        $order->wholesaler_id = $wholesaler->entity_id;//默认值
        $order->wholesaler_name = $wholesaler->store_name;
        $order->state = SalesFlatOrder::STATE_PROCESSING;
        $order->status = SalesFlatOrder::STATUS_PROCESSING;
        $order->applied_rule_ids = '';
        $order->payment_method = 3;
        $order->customer_id = $customer->entity_id;
        $order->store_name = $customer->store_name;
        $order->phone = $customer->phone;
        $order->delivery_method = 2;
        $order->province = $wholesaler->province;
        $order->city = $wholesaler->city;
        $order->district = $wholesaler->district;
        $order->area_id = $customer->area_id;//应该使用用户的区域
        $order->remote_ip = '';
        $order->hold_before_state = '';
        $order->hold_before_status = '';
        $order->customer_note = '';
        $order->balance = 0;
        $order->rebates = 0;
        $order->commission = 0;
        $order->promotions = 0;
        $order->merchant_remarks = '';
        $order->total_qty_ordered = 0;
        $order->total_due = 0;
        $order->total_paid = 0;
        $order->discount_amount = 0;
        $order->total_item_count = 0;
        $order->coupon_discount_amount = 0;
        $order->shipping_amount = 0;
        $order->subtotal = 0;
        $order->grand_total = 0;
        $order->pay_time = '';
        $order->complete_at = '';
        $order->expire_time = date('Y-m-d H:i:s', strtotime("+1month", $timestamp));
        $order->created_at = date('Y-m-d H:i:s', $timestamp);
        $order->remind_count = 0;
        $order->remind_at = '';
        $order->updated_at = date('Y-m-d H:i:s', $timestamp);
        $order->receipt = 0;
        $order->receipt_total = 0;
        $order->rebates_lelai = 0;
        $order->source = 0;
        $order->contractor_id = 0;
        $order->contractor = 0;
        $order->store_label1 = 0;
        $order->storekeeper = 0;
        $order->additional_info = 0;
        $order->coupon_id = 0;
        $order->is_first_order = 0;
        $order->rebates_wholesaler = 0;
        $order->subsidies_lelai = 0;
        $order->subsidies_wholesaler = 0;
        $order->rule_apportion_lelai = 0;
        $order->rule_apportion_wholesaler = 0;
        $order->rule_apportion = 0;
        $order->customer_tag_id = $customer->tag_id;
        $order->merchant_type_id = $wholesaler->store_type;
        $order->cancel_reason = 0;
        $order->coupon_return_status = 0;
        $order->rebate_return_status = 0;
        $order->activity_id = 0;

        $products = $item['products'];
//        $this->log('$products');
//        $this->log($products);

        $total_item_count = count($products);//商品种类
        $total_qty_ordered = 0;//商品总数量
        $commission = 0;//整单佣金
        $subsidies_lelai = 0;//乐来特价补贴
        /** @var Products $product */
        $subtotal = 0;
        foreach ($products as $productId => $product) {
            if ($product['gross_profit_rate'] <> 0) {
                $grossProfitRate = ($product['gross_profit'] / $product['qty']) / $product['price'];
            } else {
                $grossProfitRate = 0;
            }
            $row_subsidies_lelai = 0;//订单商品乐来特价补贴
            $qty = $product['num'];
            $total_qty_ordered += $qty;
            $date = new Date();
            $time = $date->gmtDate();
            $orderItem = new SalesFlatOrderItem();
            $orderItem->name = $product['name'];
            $orderItem->brand = $product['brand'] ?? 'no-brand';
            $orderItem->sku = 'sku';
            $orderItem->specification = $product['specification'];
            $orderItem->barcode = $product['barcode'];
            $orderItem->first_category_id = $product['first_category_id'];
            $orderItem->second_category_id = $product['second_category_id'];
            $orderItem->third_category_id = $product['third_category_id'];
            $orderItem->image = $product['image'];
            $orderItem->wholesaler_id = $product['wholesaler_id'];
            $orderItem->product_id = $product['entity_id'];
            $orderItem->product_type = 0;
            $orderItem->row_total = $product['price'] * $qty;
            $orderItem->price = $product['price'];
            $orderItem->original_price = $product['price'];
            if (isset($this->subsidies[$product['month']])) {
                $subsidies = $product['price'] * $this->subsidies[$product['month']];
                $orderItem->original_price = $product['price'] + $subsidies;
                $row_subsidies_lelai = $row_subsidies_lelai + $subsidies * $qty;//乐来特价补贴
            } elseif (isset($this->subsidies[$orderMonth]) && $product['gross_profit_rate'] == 0) {//无毛利商品，订单在指定月份的，也要生成特价补贴
                $subsidies = $product['price'] * $this->subsidies[$orderMonth];
                $orderItem->original_price = $product['price'] + $subsidies;
                $row_subsidies_lelai = $row_subsidies_lelai + $subsidies * $qty;//乐来特价补贴
            } else {
                $this->log($product, 'product.log');
            }
            $orderItem->product_options = '';
            $orderItem->tags = '';
            $orderItem->qty = $qty;
            $orderItem->is_calculate_lelai_rebates = 0;
            $orderItem->rebates = 0;
            $orderItem->rebates_calculate = 0;
            $orderItem->commission = $grossProfitRate * $orderItem->row_total;
            $orderItem->commission_percent = $grossProfitRate * 100;
            if (isset($this->subsidies[$product['month']])) {
                $orderItem->commission = ($grossProfitRate + $this->subsidies[$product['month']]) * $orderItem->row_total;
                $orderItem->commission_percent = $grossProfitRate + $this->subsidies[$product['month']];
                $orderItem->commission_percent *= 100;
            } elseif (isset($this->subsidies[$orderMonth]) && $product['gross_profit_rate'] == 0) {//无毛利商品，订单在指定月份的，也要生成佣金
                $orderItem->commission = ($grossProfitRate + $this->subsidies[$orderMonth]) * $orderItem->row_total;
                $orderItem->commission_percent = $grossProfitRate + $this->subsidies[$orderMonth];
                $orderItem->commission_percent *= 100;
            } else {
                $this->log($product, 'product.log');
            }
            $orderItem->created_at = $time;
            $orderItem->updated_at = $time;
            $orderItem->subsidies_wholesaler = 0;
            $orderItem->subsidies_lelai = $row_subsidies_lelai;
            $orderItem->origin = $product['origin'];
            $orderItem->promotion_text = '';
            $orderItem->buy_path = '';
            $orderItem->activity_id = 0;
            $orderItem->additional_info = 0;
            $orderItem->rule_apportion = 0;
            $orderItem->rule_apportion_lelai = 0;
            $orderItem->rule_apportion_wholesaler = 0;
//            $orderItem->rule_apportion_order_act_lelai = 0;
//            $orderItem->rule_apportion_order_coupon_lelai = 0;
//            $orderItem->rule_apportion_products_act_lelai = 0;
//            $orderItem->rule_apportion_products_coupon_lelai = 0;
            $orderItem->rebates_lelai = 0;
            $orderItem->rebates_calculate_lelai = 0;
            $orderItem->created_at = date('Y-m-d H:i:s', $timestamp);
            $orderItem->updated_at = date('Y-m-d H:i:s', $timestamp);
            $order->addItem($orderItem);
            $commission += $orderItem->commission;
            $subsidies_lelai += $row_subsidies_lelai;
            $subtotal += $orderItem->row_total;
        }
        if (abs($orderData->gmv - $subtotal) > 0.00001) {
            $this->log('Invalid Order:', 'invalid.log');
            $this->log($orderData, 'invalid.log');
            $this->log('Delta:' . ($orderData->gmv - $subtotal), 'invalid.log');
            $this->log($subtotal, 'invalid.log');
            $this->log($products, 'invalid.log');
        }
        $order->subtotal = $subtotal;
        $order->grand_total = $subtotal;
        $order->total_qty_ordered = $total_qty_ordered;
        $order->total_item_count = $total_item_count;
        $order->commission = $commission;
        $order->subsidies_lelai = $subsidies_lelai;
        $order->save();
        $customer->addDay($orderData->day);
    }

    /**
     * @param $city
     * @return LeMerchantStore
     */
    protected function getWholesaler($city)
    {
        $stores = LeMerchantStore::find()
            ->where(['store_type' => 7])
            ->andWhere(['city' => $city])
            ->all();
        shuffle($stores);//打乱数组,随机取一个供货商
        return current($stores);
    }

    /**
     * 根据日期获取可用的用户
     * @param $day
     * @return bool|LeCustomers
     * @throws \Exception
     */
    protected function getCustomer($day)
    {
        $found = false;
        shuffle($this->customers);
        /** @var LeCustomers $customer */
        foreach ($this->customers as $customer) {
            if ($customer->testDay($day)) {
                $found = $customer;
                break;
            }
        }

        if (!$found) {
            $this->log($day);
            $this->log($this->customers);
            throw new \Exception('未找到用户');
        }
        return $found;
    }

    /**
     *
     * @param $day
     * @return false|int
     */
    protected function getOrderCreatedAtTimestamp($day)
    {
        $time = strtotime($day);
        $day = date('j', $time);
        $month = date('n', $time);
        $year = date('Y', $time);
        return mktime(mt_rand(0, 15), mt_rand(0, 59), mt_rand(0, 59), $month, $day, $year);
    }

    /**
     * 获取指定日期，随机时间的随机订单号
     * @param $timestamp
     * @return string
     */
    public function getIncrementIdByDay($timestamp)
    {
        list($s1, $s2) = explode(' ', microtime());
        $millisecond = explode('.', $s1);
        $mill = substr($millisecond[1], 0, 5);
        return sprintf('%s%s', date('ymdHis', $timestamp), $mill);
    }

    protected function log($msg, $filename = 'writer.log')
    {
        ToolsAbstract::log($msg, $filename);
    }

    protected function echo($msg)
    {
        echo $msg . "\r";
    }
}