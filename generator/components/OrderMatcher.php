<?php

namespace generator\components;

use framework\components\ToolsAbstract;
use generator\components\math\Combinatorics;
use generator\models\Orders;
use generator\models\Product;
use generator\models\Products;

/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-21
 * Time: 上午10:19
 */
class OrderMatcher
{
    /**
     * 集合
     * @var array
     */
    protected $collection;

    /**
     * 可选的组合数量。即，一个数字可以由多少个数字组成
     * @var array
     */
    protected $availableSize = [1, 2, 3];

    protected $size = 3;

    protected $number;
    /**
     * 均值
     * @var integer|float
     */
    protected $mean;

    /**
     * 中值
     * @var integer|float
     */
    protected $median;

    /**
     * 最大值
     * @var integer|float
     */
    protected $max;

    /**
     * 最小值
     * @var integer|float
     */
    protected $min;

    /**
     * 组合器
     * @var
     */
    protected $combiner;

    /**
     * 目标值
     * @var integer|float
     */
    protected $targetValue;

    /**
     * 最大重试次数
     * @var integer
     */
    protected $maxTries;

    protected $debug = true;

    /**
     * 毛利商品
     * @var array
     */
    protected $profitProducts = [];

    /**无毛利商品
     * @var array
     */
    protected $nonProfitProducts = [];

    /**
     * 订单列表
     * @var array
     */
    protected $orders = [];

    /**
     * 非盈利商品集合
     * @var array
     */
    protected $nonProfitProductCollection = [];

    protected $matchedCollection = [];

    protected $progressBarPrefix = '';
    protected $progressBarMedium = '';
    protected $progressBarSuffix = '';
    protected $city;
    protected $month;

    /**
     * 数值匹配
     */
    public function match()
    {
        try {
            $this->prepareProfitProducts();
            $this->prepareNotProfitProducts();
            $this->prepare();
            $this->prepareOrders();
            $this->matchOrders();
        } catch (\Throwable $e) {
            $this->logError($e);
        }
    }

    protected function prepareProfitProducts()
    {
        /**
         * @var  $key
         * @var Product $product
         */
        foreach ($this->profitProducts as $key => $product) {
            $this->collection[] = $product->price;
            $product->setUsed(0);
        }
    }

    protected function prepareNotProfitProducts()
    {
        /**
         * @var  $key
         * @var Product $product
         */
        foreach ($this->nonProfitProducts as $product) {
            $this->collection[] = $product->price;
            $this->nonProfitProductCollection[$product->entity_id] = $product->price;
            $product->setUsed(0);
            $product->qty = 1000;
        }
    }

    protected function prepareOrders()
    {
        /** @var Orders $order */
        foreach ($this->orders as $key => $order) {
            if ($order->qty > 1) {
                $this->spiltOrder($order);
            }
        }
//        $this->log(count($this->orders), 'orders.log');
//        foreach ($this->orders as $order) {
//            $this->log($order->toArray(), 'orders.log');
//        }
    }

    /**
     * 拆分订单
     * @param Orders $order
     */
    protected function spiltOrder($order)
    {
        try {
            $gmv = $order->gmv;
            $multiple = ceil($gmv / $this->mean);
            $qty = $order->qty;
            $minQty = 3 + $qty;
            if ($multiple <= $minQty) {
                throw new \Exception("订单金额：$gmv ，无法拆分为：$minQty 个订单！");
            }
//            $this->log('$multiple:' . $multiple);
            $seeds = [];
            for ($i = 0; $i < $qty; $i++) {
                $seeds[] = mt_rand(3, floor($multiple / $qty));
            }
//            $this->log($seeds);
            $usedGmv = 0;
            foreach ($seeds as $index => $seed) {
                $newOrder = new Orders();
                $newOrder->entity_id = 0;
                $newOrder->city = $order->city;
                $newOrder->city_name = $order->city_name;
                $newOrder->day = $order->day;
                $newOrder->qty = 1;
                $newOrder->gmv = ceil($seed * $this->mean);
                $newOrder->setParentId($order->entity_id);
                $newOrder->setSeed($seed);
                if (($qty - 1) == $index) {
                    $newOrder->gmv = $gmv - $usedGmv;
                } else {
                    $usedGmv += $newOrder->gmv;
                }
                $this->orders[] = $newOrder;
            }
//            $this->log($this->orders);
        } catch (\Throwable $e) {
            $this->logError($e);
        }
    }

    protected function matchOrders()
    {
        $timeStart = microtime(true);
        $total = count($this->orders);
        /** @var Orders $order */
        foreach ($this->orders as $index => $order) {
            $index += 1;
            if ($order->qty > 1) {
                continue;
            }
            $percent = floor($index * 20 / $total);
            $this->progressBarPrefix = sprintf("City:%s,Month:%s Matching. %3s/%-3s[%s>%s] %s%% \t", $this->city, $this->month, $index, $total, str_repeat('=', $percent), str_repeat('-', 20 - $percent), $percent * 5);
            $this->flushProgressBar();
            $result = $this->matchOrder($order);
//            $this->flushProgressBar(true);
            if ($result) {
                $this->matchedCollection[] =
                    [
                        'products' => $result,
                        'order' => $order,
                    ];
            }
        }
        $timeEnd = microtime(true);
        $this->progressBarMedium = '';
        $this->progressBarPrefix = sprintf("City:%s,Month:%s Matching. %3s/%-3s[%s>%s] %s%% %ssec %-50s \t", $this->city, $this->month, $total, $total, str_repeat('=', 20), str_repeat('-', 0), 100, round($timeEnd - $timeStart, 2), '');
//        $this->progressBarMedium = '';
        $this->flushProgressBar(true);
        $this->log('[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[BOB');
        /** @var Products $profitProduct */
        foreach ($this->profitProducts as $profitProduct) {
            $this->log($profitProduct->toArray());
        }
        $this->log('[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[EOB');
    }

    /**
     * @param Orders $order
     * @return mixed
     */
    protected function matchOrder($order)
    {
        $this->log('===========================ORDER-' . ($order->entity_id ? 'OrderId:' . $order->entity_id : 'ParentId:' . $order->getParentId()) . ' BOF =========================================');
        $this->log('===========================ORDER-' . $order->day . '=========================================');
        $gmv = $order->gmv;
        $result = $this->matchCombination($gmv);
        $this->log('===========================ORDER-' . ($order->entity_id ? 'OrderId:' . $order->entity_id : 'ParentId:' . $order->getParentId()) . ' BOF =========================================');

        return $result;
    }

    protected function matchCombination($targetValue)
    {
        $avaSize = [3, 2, 1];
        $found = false;
        $matchedCombination = [];
        $matchedCombinationArray = [];
        while (true) {
            foreach ($avaSize as $size) {
                $restTotal = 0;
                while (true) {
                    list($selectedValue, $profitProductList) = $this->getProfitProduct($targetValue);
                    $this->log('毛利商品小计:' . $selectedValue);
                    $restTotal = $this->formatPrice($targetValue - $selectedValue);
                    if ($restTotal > 0) {
                        $matchedCombination['profit'] = $profitProductList;
                        break;
                    }
                }
                $this->log('剩余:' . $restTotal);
                $this->log('MaxTrier:' . $this->maxTries . ',size:' . $size);
                list($found, $combination) = $this->getRestTotalCombination($restTotal, $size);
                if ($found === true) {
//                    $this->log($combination);
                    $matchedCombination['non-profit'] = $combination;
                    break;
                }
            }
            if ($found === true) {
                $this->log('Hit');
                $this->log('目标值：' . $targetValue);
                $this->log('OK');
                if (isset($matchedCombination['profit'])) {
                    /** @var Product $product */
                    foreach ($matchedCombination['profit'] as $product) {
                        $data = $product->toArray();
                        $data['num'] = $product->getSelected();
                        $matchedCombinationArray[$product->entity_id] = $data;
                        $product->setUsed($product->getUsed() + $product->getSelected());
                        $product->setSelected(0);
                    }
                    $deltaPrice = 0;
                    /** @var Product $product */
                    foreach ($matchedCombination['non-profit'] as $key => $product) {
                        $data = $product->toArray();
                        $data['num'] = 1;
                        if ($key == 0 && $product->delta <> 0) {
                            $deltaPrice = $product->delta;//获取价格差异
                            $product->delta = 0;//重置差值
                            $this->log('Delta price：' . $deltaPrice);
                        }
                        if (isset($matchedCombinationArray[$product->entity_id])) {//无毛利的商品可能已经被使用
                            $matchedCombinationArray[$product->entity_id]['num'] += $data['num'];
                            //当无毛利商品被多次使用时，需要将微小的价格差异转移到其他商品上面
                        } else {
                            if ($deltaPrice <> 0) {
                                $data['@price'] = $data['price'] + $deltaPrice;
                                $data['price_'] = $data['price'];
                                $data['price'] = $data['@price'];

                                $deltaPrice = 0;
                                $this->log('Delta price product:');
                                $this->log($data);
                            }
                            $matchedCombinationArray[$product->entity_id] = $data;
                        }
                        $product->setUsed($product->getUsed() + $product->getSelected());
                        $product->setSelected(0);
                    }
                }
                $this->log($matchedCombinationArray);
                break;
            }
            if ($this->maxTries-- <= 0) {
                break;
            }
            break;
        }
        if ($found === false) {
            $this->log('NOT-FOUND');
            return false;
        }
        return $matchedCombinationArray;
    }


    protected function getRestTotalCombination($total, $size)
    {
        $this->log(__METHOD__);
        $this->log('Total:' . $total);
        $found = false;
        $item = null;
        $listed = [];
        $deltaPrice = 1;
        $deltaPriceItem = null;
        $count = 0;
        $needDelta = false;
        foreach ($this->getCombiner()->combinations($this->nonProfitProductCollection, $size) as $key => $item) {
            $count++;
            $this->progressBarMedium = sprintf("Searching. Total:%s,size:%s,Round:%-50s", $total, $size, $count);
            $this->flushProgressBar();
            $sum = array_sum($item);
            if (abs($total - $sum) < 0.001) {
                $found = true;
                break;
            }

            //允许差值在5毛以内
            if (abs($total - $sum) < 1) {
                if (abs($deltaPrice) > abs($total - $sum)) {
                    $deltaPrice = $total - $sum;
                    $deltaPriceItem = $item;
                }
            }
        }

        if ($found === false && abs($deltaPrice) < 1) {
            //已经尝试了10w次，未找到误差在0.001的组合，跳出，但是找到了误差小于1的组合了
            $found = true;
            $item = $deltaPriceItem;
            $needDelta = true;
        }

        if ($found) {
            $this->progressBarMedium = sprintf("Hit. Total:%s,Delta-price:%s,size:%s,Round:%-50s", $total, $needDelta ? $deltaPrice : 0, $size, $count);
        }

        if ($found) {
            foreach ($item as $productId => $price) {
                /** @var Product $nonProfitProduct */
                foreach ($this->nonProfitProducts as $nonProfitProduct) {
                    if ($productId == $nonProfitProduct->entity_id) {
                        //只在第一个商品上面记录微小的价格差异
                        if ($needDelta) {
                            $nonProfitProduct->delta = $deltaPrice;
                            $deltaPrice = 0;
                        }
                        $listed[] = $nonProfitProduct;
                        break;
                    }
                }
            }
        }

        return [$found, $listed];
    }

    protected function calcRestTotal($targetValue, $selectedValue)
    {
        $n = 0;
        $restTotal = $targetValue;
        while (true) {
            if ($restTotal < 0 || ($restTotal <= 3 * $this->mean) && ($restTotal >= $this->mean)) {
                break;
            }
            $n++;
            $restTotal = $targetValue - $selectedValue * $n;
        }
        return [$n, $restTotal];
    }

    /**
     * @param $targetValue
     * @return array
     * @throws \Exception
     */
    protected function getProfitProduct($targetValue)
    {
        $listed = [];
        $this->log($targetValue);
        $selectedValue = 0;
        $full = false;
        /** @var Product $profitProduct */
        foreach ($this->profitProducts as $profitProduct) {
            if (intval($profitProduct->qty) === intval($profitProduct->getUsed()) || (intval($profitProduct->getUsed()) + intval($profitProduct->getSelected())) === intval($profitProduct->qty)) {
                continue;
            }
            $this->log('毛利商品：');
            $this->log($profitProduct->toArray());
            list($full, $num) = $this->calcProfitProductNum($profitProduct, $targetValue - $selectedValue);
            $this->log('$full:' . $full . ',$num:' . $num . ',used:' . $profitProduct->getUsed() . ',qty:' . $profitProduct->qty);
            if (($profitProduct->getUsed() + $num) > $profitProduct->qty) {
                throw new \Exception('匹配失败');
            }
            $profitProduct->setSelected($num);
            $selectedValue += $profitProduct->price * $profitProduct->getSelected();
            $listed[] = $profitProduct;
            if ($full) {
                break;
            }
        }

        if (!$full) {
            $this->log('毛利商品不足，将从其他商品中补充！');
            list($nonProfitSelectedValue, $nonProfitList) = $this->getNonProfitProduct($targetValue - $selectedValue);
            foreach ($nonProfitList as $nonProfitProduct) {
                $listed[] = $nonProfitProduct;
            }
            $selectedValue += $nonProfitSelectedValue;
        }
        return [$selectedValue, $listed];
    }

    protected function getNonProfitProduct($targetValue)
    {
        $this->log(__METHOD__);
        $listed = [];
        $this->log($targetValue);
        $selectedValue = 0;
        /** @var Product $nonProfitProduct */
        foreach ($this->nonProfitProducts as $nonProfitProduct) {
            if ($nonProfitProduct->qty === $nonProfitProduct->getUsed() || ($nonProfitProduct->getUsed() + $nonProfitProduct->getSelected()) === $nonProfitProduct->qty) {
                continue;
            }
            $this->log('无毛利商品：');
            $this->log($nonProfitProduct->toArray());
            list($full, $num) = $this->calcProfitProductNum($nonProfitProduct, $targetValue - $selectedValue);
            if ($num == -1) {
                $this->log('商品不合适，继续尝试下一个');
                continue;
            }
            $this->log('$full:' . $full . ',$num:' . $num . ',used:' . $nonProfitProduct->getUsed() . ',qty:' . $nonProfitProduct->qty);
            if (($nonProfitProduct->getUsed() + $num) > $nonProfitProduct->qty) {
                throw new \Exception('无毛利商品，匹配失败');
            }
            $nonProfitProduct->setSelected($num);
            $selectedValue += $nonProfitProduct->price * $nonProfitProduct->getSelected();
            $listed[] = $nonProfitProduct;
            if ($full) {
                break;
            }
        }
        return [$selectedValue, $listed];
    }


    /**
     * @param Product $product
     * @param float $targetValue
     * @return array
     */
    protected function calcProfitProductNum($product, $targetValue)
    {
        $price = $product->price;
        $restTotal = $targetValue;
        $qty = $product->qty;
        $used = $product->getUsed();
        $n = 0;
        $full = false;
        while (true) {
            if (($restTotal <= 4 * $this->mean) && ($restTotal >= 2 * $this->mean)) {
                $full = true;
                break;
            }
            if ($restTotal < (2 * $this->mean)) {
                //超出了
                while ($n--) {
                    $restTotal = $targetValue - $price * $n;
                    if ($restTotal > 0 && $restTotal <= 3 * $this->mean) {
                        $full = true;
                        break;
                    }
                }
                break;
            }
            $n++;
            if ($n <= ($qty - $used)) {
                $restTotal = $targetValue - $price * $n;
            } else {
                //超出了
                $n--;
                break;
            }
        }
        return [$full, $n,];
    }

    protected function reset()
    {
        $this->maxTries = 3;
        $this->min = null;
        $this->max = null;
        $this->mean = null;
        $this->median = null;
        $this->progressBarPrefix = '';
        $this->progressBarMedium = '';
        $this->progressBarSuffix = '';
    }

    protected function prepare()
    {
        $this->reset();
        $this->sortCollection();
        $this->calcMean();
        $this->calcMedian();
        $this->calcMax();
        $this->calcMin();
        $this->log('均值:' . $this->mean);
        $this->log('中值:' . $this->median);
        $this->log('最大值:' . $this->max);
        $this->log('最小值:' . $this->min);
    }

    /**
     * 计算中值
     */
    protected function calcMedian()
    {
        $arr = array_values($this->getCollection());
        $count = count($arr); //total numbers in array
        $middleValue = floor(($count - 1) / 2); // find the middle value, or the lowest middle value
        if ($count % 2) { // odd number, middle is the median
            $median = $arr[$middleValue];
        } else { // even number, calculate avg of 2 medians
            $low = $arr[$middleValue];
            $high = $arr[$middleValue + 1];
            $median = (($low + $high) / 2);
        }
        $this->median = $median;
    }

    /**
     * 计算均值
     * @param
     */
    protected function calcMean()
    {
        $arr = $this->getCollection();
        $count = count($arr);
        $total = array_sum($arr);
        $this->mean = ($total / $count);
    }

    /**
     * 计算最大值
     */
    protected function calcMax()
    {
        $this->max = end($this->collection);
    }

    /**
     * 计算最大值
     */
    protected function calcMin()
    {
        reset($this->collection);
        $this->min = current($this->collection);
    }

    /**
     * 对数据集合进行排序，ASC
     */
    protected function sortCollection()
    {
        $start = microtime(true);
        asort($this->collection, SORT_NUMERIC);
        $end = microtime(true);
        $this->log(__METHOD__ . ' elapsed: ' . ($end - $start));
    }

    /**
     * @param mixed $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function getCollection(): array
    {
        return $this->collection;
    }

    /**
     * 组合器
     * @return Combinatorics
     */
    protected function getCombiner()
    {
        if (!$this->combiner) {
            $this->combiner = new Combinatorics();
        }
        return $this->combiner;
    }

    /**
     * @param $msg
     * @param string $filename
     */
    private function log($msg, $filename = 'matcher.log')
    {
        if ($this->debug) {
            ToolsAbstract::log($msg, $filename);
        }
    }

    private function logError($e)
    {
        ToolsAbstract::logError($e, 'matcher_error.log');
    }

    /**
     * @param array $orders
     */
    public function setOrders(array $orders)
    {
        $this->orders = $orders;
    }

    /**
     * @param array $profitProducts
     */
    public function setProfitProducts(array $profitProducts)
    {
        $this->profitProducts = $profitProducts;
    }

    /**
     * @param array $nonProfitProducts
     */
    public function setNonProfitProducts(array $nonProfitProducts)
    {
        $this->nonProfitProducts = $nonProfitProducts;
    }

    private function arrayToString($array)
    {
        return print_r($array, true);
    }

    private function formatPrice($price)
    {
        return round($price, 1);
    }

    /**
     * @return array
     */
    public function getMatchedCollection(): array
    {
        return $this->matchedCollection;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param mixed $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * @return string
     */
    public function getProgressBarPrefix(): string
    {
        return $this->progressBarPrefix;
    }

    /**
     * @param string $progressBarPrefix
     */
    public function setProgressBarPrefix(string $progressBarPrefix)
    {
        $this->progressBarPrefix = $progressBarPrefix;
    }

    /**
     * @return string
     */
    public function getProgressBarMedium(): string
    {
        return $this->progressBarMedium;
    }

    /**
     * @param string $progressBarMedium
     */
    public function setProgressBarMedium(string $progressBarMedium)
    {
        $this->progressBarMedium = $progressBarMedium;
    }

    /**
     * @return string
     */
    public function getProgressBarSuffix(): string
    {
        return $this->progressBarSuffix;
    }

    /**
     * @param string $progressBarSuffix
     */
    public function setProgressBarSuffix(string $progressBarSuffix)
    {
        $this->progressBarSuffix = $progressBarSuffix;
    }


    protected function flushProgressBar($newLine = false)
    {
        if ($newLine) {
            echo $this->progressBarPrefix . $this->progressBarMedium . $this->progressBarSuffix . "\n";
        } else {
            echo $this->progressBarPrefix . $this->progressBarMedium . $this->progressBarSuffix . "\r";
        }
    }
}