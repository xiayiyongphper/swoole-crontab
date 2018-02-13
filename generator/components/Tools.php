<?php

namespace generator\components;

use framework\components\Date;
use framework\components\ToolsAbstract;
use JsonSchema\RefResolver;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use Yii;

/**
 * public function
 */
class Tools extends ToolsAbstract
{

    //生成随机数
    const CHARS_LOWERS = 'abcdefghijklmnopqrstuvwxyz';
    const CHARS_UPPERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHARS_DIGITS = '0123456789';

    public static $_categoryIconUrlPre = 'http://assets.lelai.com/images/booking/category/icon/v1/';
    public static $_categoryMap = array(
        2 => '饼干糕点',
        127 => '方便速食',
        213 => '日用杂货',
        161 => '洗护成人',
        269 => '厨房用品',
        31 => '休闲零食',
        80 => '酒水饮料',
        103 => '乳品茶冲',
        413 => '雪糕冷链',
        421 => '增值服务',
        429 => '生鲜食品',
        450 => '中外名酒',
    );

    public static function getCategoryIconUrl($cid)
    {
        if (isset(self::$_categoryMap[$cid])) {
            return self::$_categoryIconUrlPre . $cid . '.png';
        } else {
            return false;
        }
    }

    /**
     * @param $url
     * Author Jason Y. wang
     * 获得图片地址中的高度
     * @return mixed|string
     */
    public static function getImageHeightByUrl($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $params = [];
        if (is_string($query) && strlen($query) > 0) {
            $parts = explode('&', $query);
            if ($parts) {
                foreach ($parts as $part) {
                    $items = explode('=', $part);
                    if (count($items) == 2) {
                        $params[$items[0]] = $items[1];
                    }
                }
            }
        }

        return isset($params['height']) ? $params['height'] : '';
    }


    public static function getCategoryByProducts($productList)
    {
        // map
        $storeHas = array();
        foreach ($productList as $key => $item) {
            $value = $item;
            $hash = $value['first_category_id'] . '|' . $value['second_category_id'] . '|' . $value['third_category_id'];
            $storeHas[$hash] = 1;
        }
        //print_r(json_encode($storeHas));

        // PMS全树
        $pmsCategory = Tools::proCate();
        $pmsCategory = [
            'id' => 1,
            'parent_id' => 0,
            'name' => 'Root',
            'path' => '1',
            'level' => '0',
            'child_category' => $pmsCategory,
        ];

        foreach ($pmsCategory['child_category'] as $index => $fc) {

            // 有儿子分类则继续
            if (is_array($fc['child_category']) || count($fc['child_category'])) {

                foreach ($fc['child_category'] as $index_2 => $sc) {
                    // 有儿子分类则继续
                    if (is_array($sc['child_category']) || count($sc['child_category'])) {
                        foreach ($sc['child_category'] as $index_3 => $tc) {
                            $hash = $fc['id'] . '|' . $sc['id'] . '|' . $tc['id'];
                            //原来的过滤分类
                            if (!isset($storeHas[$hash])) {
                                unset($pmsCategory['child_category'][$index]['child_category'][$index_2]['child_category'][$index_3]);
                            } else {
                                // 本身最小的子分类儿子节点去掉
                                unset($pmsCategory['child_category'][$index]['child_category'][$index_2]['child_category'][$index_3]['child_category']);
                            }

                        }
                    }
                    // 儿子被删光了,本身也干掉
                    if (!count($pmsCategory['child_category'][$index]['child_category'][$index_2]['child_category'])) {
                        unset($pmsCategory['child_category'][$index]['child_category'][$index_2]);
                    }
                }
            }
            // 儿子被删光了,本身也干掉
            if (!count($pmsCategory['child_category'][$index]['child_category'])) {
                unset($pmsCategory['child_category'][$index]);
            }
        }

        // 去掉key值，避免ios客户端崩掉getStoreProductDetail
        $pmsCategory['child_category'] = array_values($pmsCategory['child_category']);
        foreach ($pmsCategory['child_category'] as $index => $fc) {
            $pmsCategory['child_category'][$index]['child_category'] = array_values($pmsCategory['child_category'][$index]['child_category']);
            foreach ($pmsCategory['child_category'][$index]['child_category'] as $index_2 => $sc) {
                $pmsCategory['child_category'][$index]['child_category'][$index_2]['child_category'] = array_values($pmsCategory['child_category'][$index]['child_category'][$index_2]['child_category']);
            }
        }

        // 写缓存
        //$redis->set($cacheKey, serialize($pmsCategory['child_category']), 600);// 10分钟

        return $pmsCategory;
    }

    /**
     * Function: getCategoryLevelByID
     * Author: Jason Y. Wang
     * 计算一个分类的level
     * @param $category_id
     * @return null
     */
    public static function getCategoryLevelByID($category_id)
    {
        $categories = Redis::getPMSCategories();
        foreach ($categories as $key => $category) {
            $category = unserialize($category);
            if ($category['id'] == $category_id) {
                return $category['level'];
            }
        }
        return null;
    }

    /**
     * 取产品分类
     */

    public static function proCate()
    {
        /** @var \yii\Redis\Cache $redis */
        $redis = Yii::$app->redisCache;
        //通过SD库接口取产品分类,结果存放redis
        if ($redis->exists("pro_cate") === false) {
            $categories = Redis::getPMSCategories();
            $tree = self::collectionToArray($categories, 0);
            $redis->set("pro_cate", serialize($tree), 3600);
        }
        $category = unserialize($redis->get("pro_cate"));
        return ($category && isset($category[0]) && isset($category[0]['child_category'])) ? $category[0]['child_category'] : [];

    }


    /**
     * @param $collection
     * @param $parentId
     * @return array
     */
    protected static function collectionToArray($collection, $parentId)
    {
        $categories = array();
        foreach ($collection as $key => $category) {
            $category = unserialize($category);
            if ($category['parent_id'] == $parentId) {
                $categories[] = array(
                    'id' => $category['id'],
                    'parent_id' => $category['parent_id'],
                    'name' => $category['name'],
                    'path' => $category['path'],
                    'level' => $category['level'],
                    'child_category' => self::collectionToArray($collection, $category['id']),
                );
                unset($collection[$key]);
            }
        }
        return $categories;
    }

    /**
     * 递归取某个分类下的所有子类ID
     *
     * @param string $proclass 商品分类
     * @param int $cid 待查找子类的ID
     * @param array $child 存放被查出来的子类ID
     */
    public static function cateChild($proclass, $cid, &$child)
    {

        //k:父分类ID  v:子分类值
        foreach ($proclass as $k => $v) {
            if ($v['parent_id'] == $cid || $v['id'] == $cid) {

                $child[] = $v['id'];

                self::cateChild($v['child_category'], $v['id'], $child);

            } else {
                foreach ($v['child_category'] as $key => $val) {
                    if ($val['id'] == $cid) {
                        $child[] = $val['id'];
                    }
                }
            }
        }
    }

    /**
     * Function: getCategoryIdByCid
     * Author: Jason Y. Wang
     *根据所有分类和给定分类确定给定分类所属的一二三级分类
     * @param $cat
     * @param $cid
     * @param $child
     * @param $index
     *
     */
    public static function getCategoryIdsByCid($cat, $cid, &$child, $index = 0, &$flag = false)
    {
        //k:父分类ID  v:子分类值
        foreach ($cat as $k => $v) {
            if ($flag == true) {
                return;
            }
            $child[$index] = $v['id'];
            if ($v['id'] == $cid) {
                $flag = true;
                return;
            }
            self::getCategoryIdsByCid($v['child_category'], $cid, $child, $index + 1, $flag);
        }
    }

    /**
     * 返回当前分类ID大类目和下一级分类
     */

    public static function curCate($cid, &$curCateList)
    {

        $cate = self::proCate();
        foreach ($cate as $k => $v) {
            if ($v['id'] == $cid) {
                $curCateList['id'] = $v['id'];
                $curCateList['name'] = $v['name'];
                foreach ($v['child_category'] as $key => $val) {
                    $curCateList['child_category'][$key]['id'] = $val['id'];
                    $curCateList['child_category'][$key]['name'] = $val['name'];
                }
            } else {
                foreach ($v['child_category'] as $key => $val) {

                    if ($val['id'] == $cid) {
                        self::curCate($val['parent_id'], $curCateList);
                    }

                }
            }
        }


    }

    /**
     * 返回指定分类导航链接
     */
    public static function navCate($cid)
    {
        $curCateList = $nav = array();

        self::curCate($cid, $curCateList);

        $nav[0]['id'] = $curCateList['id'];

        $nav[0]['name'] = $curCateList['name'];
        if ($curCateList['id'] != $cid) {

            foreach ($curCateList['child_category'] as $v) {

                if ($cid == $v['id']) {

                    $nav[1]['id'] = $v['id'];

                    $nav[1]['name'] = $v['name'];

                }

            }
        }

        return $nav;
    }

    public static function numberFormat($number, $precision = 0)
    {
        return number_format($number, $precision, null, '');
    }

    /**
     * 取商品价格，特殊商品返回特殊价格
     * 如果是特价商品返回特价,不然返回原价
     */
    public static function getPrice($val)
    {
        $specialPrice = $val['special_price'];
        $price = $val['price'];

        if ($specialPrice > 0
            && $specialPrice < $price
            && Tools::dataInRange($val['special_from_date'], $val['special_to_date'])
        ) {
            $finalPrice = $specialPrice;
        } else {
            $finalPrice = $price;
        }

        return self::numberFormat($finalPrice, 2);
    }

    /**
     * 判断是否为特价
     * @param $val
     * @return bool
     */
    public static function getIsSpecial($val)
    {
        if ($val['special_price'] > 0
            && $val['special_price'] < $val['price']
            && Tools::dataInRange($val['special_from_date'], $val['special_to_date'])
        ) {
            return true;
        }
        return false;
    }

    /**
     * 判断指定点经纬度是否在配送区域
     * 参照点是否在多边形内部算法
     * 方法：求解通过该点的水平线与多边形各边的交点
     * 结论：单边交点为奇数，成立!
     * $p指定点经纬度
     * $region 多边形点经纬度
     */

    public static function ptInPolygon($p, $region)
    {
        $n = 0;
        $nCount = count($region);
        for ($i = 0; $i < $nCount; $i++) {
            $p1 = $region[$i];
            $p2 = $region[($i + 1) % $nCount];

            //求P与P1P2水平交点
            if ($p1['y'] == $p2['y']) continue;   //两点平行

            if ($p['y'] < min($p1['y'], $p2['y'])) continue;//交点在P1P2延长线

            if ($p['y'] > max($p1['y'], $p2['y'])) continue;//交点在P1P2延长线

            //求交点X的坐标
            $x = ($p['y'] - $p1['y']) * ($p2['x'] - $p1['x']) / ($p2['y'] - $p1['y']) + $p1['x'];

            if ($x > $p['x']) $n++; //统计单边交点

        }
        return ($n % 2 == 1);
    }

    public static function getImage($gallery, $size = '600x600', $single = true)
    {
        $gallery = explode(';', $gallery);
        $search = ['source', '600x600', '180x180'];
        if ($single) {
            return str_replace($search, $size, $gallery[0]);
        } else {
            $images = array();
            foreach ($gallery as $image) {
                $images[] = str_replace($search, $size, $image);
            }
            return $images;
        }
    }

    public static function formatPrice($price)
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * Function: getCategoryByTcids
     * Author: Jason Y. Wang
     * 根据所给的三级分类查找分类树
     * @param $ThirdCategoryIds
     * @return array
     */
    public static function getCategoryByTcids($ThirdCategoryIds)
    {
        // 在Redis中查找三级分类
        $categories = Redis::getCategories($ThirdCategoryIds);
        $collectionKeys = $collections = array();
        foreach ($categories as $key => $category) {
            $keys = explode('/', $category['path']);
            $collectionKeys = array_merge($collectionKeys, $keys);
        }
        $collectionKeys = array_unique($collectionKeys);
        if (count($collectionKeys)) {
            $collections = Redis::getCategories($collectionKeys);
        }

        $tree = self::unserializeCollectionToArray($collections, 0);
        return $tree[0]['child_category'];
    }


    /**
     * @param $collection
     * @param $parentId
     * @return array
     */
    protected static function unserializeCollectionToArray($collection, $parentId)
    {
        $categories = array();
        foreach ($collection as $key => $category) {
            if ($category['parent_id'] == $parentId) {
                $categories[] = array(
                    'id' => $category['id'],
                    'parent_id' => $category['parent_id'],
                    'name' => $category['name'],
                    'path' => $category['path'],
                    'child_category' => self::unserializeCollectionToArray($collection, $category['id']),
                );
                unset($collection[$key]);
            }
        }
        return $categories;
    }

    /**
     * Function: getCategoryBy
     * Author: Jason Y. Wang
     *  TODO:此方法需要修改，暂时不要使用
     * @param $products
     * @return array
     */
    public static function getCategoryBy($products)
    {
        //PMS中所有分类
        $pmsCategory = Tools::proCate();

        //商品中的所有三级分类
        $productCategory = array();
        /* @var $productModel ActiveQuery */
        $productCategorys = $products->select('third_category_id')->groupBy('third_category_id')->asArray()->all();
        foreach ($productCategorys as $key => $value) {
            $productCategory[] = $value['third_category_id'];
        }
        $Fcategory = $Scategory = $Tcategory = array();
        foreach ($pmsCategory as $first_key => $first_category) {
            foreach ($first_category['child_category'] as $second_key => $second_category) {
                foreach ($second_category['child_category'] as $third_key => $third_category) {
                    if (in_array($third_category['id'], $productCategory)) {
                        $Tcategory[] = $third_category;
                    }
                }
                if (count($Tcategory)) {
                    unset($second_category['child_category']);
                    $second_category['child_category'] = $Tcategory;
                    $Scategory[] = $second_category;
                    unset($Tcategory);
                }
            }
            if (count($Scategory)) {
                unset($first_category['child_category']);
                $first_category['child_category'] = $Scategory;
                $Fcategory[] = $first_category;
                unset($Scategory);
            }
        }
        return $Fcategory;
    }


    public static function getRandomString($len, $chars = null)
    {
        if (is_null($chars)) {
            $chars = self::CHARS_LOWERS . self::CHARS_UPPERS . self::CHARS_DIGITS;
        }
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    public static function getSchema($filename)
    {
        $file = Yii::getAlias('@service') . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($file)) {
            return 'file://' . $file;
        }
        return false;
    }

    public static function validJsonSchema($json, $schemaFilename)
    {
        $refResolver = new RefResolver(new UriRetriever(), new UriResolver());
        $schema = $refResolver->resolve(Tools::getSchema($schemaFilename));
        // Validate
        $validator = new Validator();
        $validator->check(json_decode($json), $schema);
        if ($validator->isValid()) {
            Tools::log("The supplied JSON validates against the schema.");
        } else {
            Tools::log("JSON does not validate. Violations:");
            foreach ($validator->getErrors() as $error) {
                Tools::log(sprintf("[%s] %s", $error['property'], $error['message']));
            }
        }
        return $validator->isValid();
    }

    /**
     * @param $customerId
     * @param $city
     * @param $productId
     * @return int|string
     */
    public static function getPurchasedQty($customerId, $city, $productId)
    {
        $purchasedQty = Tools::getRedis()->hGet('daily_purchase_history_' . $customerId . '_' . $city, $productId);
        return $purchasedQty ? $purchasedQty : 0;
    }

    /**
     * 判断当前是否在起止时间内
     * 此处的start和end需要输入中国时区的时间!
     * @param $start
     * @param $end
     * @param $now
     *
     * @return bool
     */
    public static function dataInRange($start = null, $end = null, $now = null)
    {
        if (!$start || !$end) {
            return false;
        }
        if (is_numeric($start)) {
            $startTime = $start;
        } else {
            $startTime = strtotime($start);
        }
        if (is_numeric($end)) {
            $endTime = $end;
        } else {
            $endTime = strtotime($end);
        }

        if (!$now) {
            $date = new Date();
            $now = $date->timestamp();
        }

        if ($startTime <= $now && $now <= $endTime) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * assortmentArray
     * Author Jason Y. wang
     * 根据一个key把数组重新组织，已节省效率,key相同则合并为一个数组
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function assortmentArray($array, $key)
    {
        $newArray = array();
        foreach ($array as $k => $v) {
            $newKey = $v[$key];
            unset($v[$key]);
            $tagsArray[$newKey][] = $v;
        }
        return $newArray;
    }

    /**
     * assortmentArray
     * Author Jason Y. wang
     * 把key拿出来，覆盖key相同的
     * @param array $array
     * @param string $key
     * @param int $flag 是否在数据中删除提取出来的key
     * @return array
     */
    public static function conversionKeyArray($array, $key, $flag = 0)
    {
        $newArray = array();
        foreach ($array as $k => $v) {
            $newKey = $v[$key];
            if ($flag == 0) {
                unset($v[$key]);
            }
            $newArray[$newKey] = $v;
        }
        return $newArray;
    }

    public static function array_values($array, $keys)
    {
        $newArray = array();
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $newArray[$key] = $array[$key];
            }
        }
        return $newArray;
    }

    public static function getProductPromotions($rule_ids)
    {
        $rules = Proxy::getSaleRule($rule_ids);
        if ($rules) {
            $promotions = $rules->toArray()['promotions'];
            $rules = self::conversionKeyArray($promotions, 'rule_id', 1);
            //Tools::log(__FUNCTION__, 'wangyang.log');
            //Tools::log($rules, 'wangyang.log');
            return $rules;
        } else {
            return [];
        }
    }

    public static function getWholesalerPromotions($wholesaler_ids)
    {
        $rules = Proxy::getSaleRule(null, $wholesaler_ids);
        $promotions = [];
        if ($rules) {
            $promotions = $rules->getPromotions();
        }
        return $promotions;
    }

    /**
     * 用于统计综合得分脚本获取供应商全部优惠信息
     * @param $wholesaler_ids
     * @return array|\service\message\common\PromotionRule[]
     */
    public static function getWholesalerAllPromotions($wholesaler_ids)
    {
        $rules = Proxy::getAllSaleRule($wholesaler_ids);
        $promotions = [];
        if ($rules) {
            $promotions = $rules->getPromotions();
        }
        return $promotions;
    }

    /**
     * @param $wholesaler_ids
     * Author Jason Y. wang
     * 无优惠券的规则才会展示
     * @return array|bool|\service\message\merchant\SaleRuleResponse
     */
    public static function getWholesalerCartPromotions($wholesaler_ids)
    {
        //拿到全部优惠，包括优惠活动和优惠券
        $rules = Proxy::getSaleRule(null, $wholesaler_ids);
        $rule_promotions = [];
        if ($rules) {
            $promotions = $rules->getPromotions();
            /** @var \service\message\common\PromotionRule $promotion */
            foreach ($promotions as $promotion) {
                //只拿到无优惠券类型的订单级活动
                if ($promotion->getCouponType() == 1 && $promotion->getType() == 3) {
                    if ($promotion->getSubsidiesLelaiIncluded() == 0) {
//                        Tools::log(__FUNCTION__,'wangyang.log');
//                        Tools::log($promotion->toArray(),'wangyang.log');
                        //特价商品不参与优惠活动的特殊处理,与JS交互  type:4表示特价商品不参与的订单级优惠
                        $promotion->setType(4);
                    }
//                    Tools::log($promotion->toArray(),'wangyang.log');
                    $rule_promotions[$promotion->getWholesalerId()] = $promotion->toArray();
                }
            }
            //Tools::log('++++++++++','wangyang.log');
            //Tools::log($rules,'wangyang.log');

        }
        return $rule_promotions;
    }

    public static function wLog($data, $filename = null)
    {
        if (!$filename) {
            $filename = 'wangyang.log';
        }
        $filename = self::getLogFilename($filename);
        $date = new Date();
        $file = self::getLogPath() . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($file, '[' . $date->date() . '] ' . print_r($data, true) . PHP_EOL, FILE_APPEND);
    }

    //获取用户所属于的分群
    public static function getCustomerBelongGroup($customer_id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ENV_GROUP_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['customer_id' => $customer_id]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json; charset=UTF-8', 'Authorization:Bearer ' . ENV_GROUP_AUTH_TOKEN));
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        $data = isset($result['data']) ? $result['data'] : [];
        if (!is_array($data)) {
            $data = [];
        }
        return $data;
    }

    /**
     * 比较app版本
     * @param $version1
     * @param $version2
     * @param $compareType
     * 可以是lt、le、gt、ge、eq
     */
    public static function compareVersion($version1, $version2, $compareType)
    {
        if (!in_array($compareType, ['lt', 'le', 'gt', 'ge', 'eq'])) {
            return false;
        }

        if (empty($version1) || empty($version2)) {
            return false;
        }

        return version_compare($version1, $version2, $compareType);
    }


}