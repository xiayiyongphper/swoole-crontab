<?php

namespace common\helpers;

use common\models\DimensionTag;

/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-4-26
 * Time: 下午5:52
 *
 * 条件1：根据相关搜索条件，从数据库拿到条件对应的用户id, 有搜索条件时：tag0,无搜索条件时：无
 * 条件2：确定选择的标签的标签 tag1
 * 条件3：开始时间对应的标签 tag2 结束时间对应的标签 tag3
 *
 * tag0=false;
 * if 条件1
 * set tag0
 * endif;
 *
 * 条件1&&条件2&&条件3 大，小，非常大
 * 条件1&&条件2
 *
 * in / !in
 */
class Tag
{
    //历史标签前缀  auto_history_tag_20_2017-04-26
    const AUTO_HISTORY_TAG_SUFFIX = 'auto_history_tag_';
    const CUSTOMER_CURRENT_TAG_SUFFIX = 'customer_current_tag_';
    //历史标签统计
    const AUTO_HISTORY_TAG_STAT_SUFFIX = 'auto_history_tag_stat_';

    //活跃上升用户
    const ACTIVE_RISE_CUSTOMERS_TAG = 1;
    //重新激活用户
    const ACTIVE_REACTIVE_CUSTOMERS_TAG = 2;
    //活跃下降用户
    const ACTIVE_DECLINE_CUSTOMERS_TAG = 3;
    //轻度流失用户
    const ACTIVE_LIGHT_LOSS_CUSTOMERS_TAG = 4;
    //重度流失用户
    const ACTIVE_SERIOUSLY_LOSS_CUSTOMERS_TAG = 5;
   //僵尸用户
    const ZOMBIE_CUSTOMERS_TAG = 6;
    //已下单新用户
    const ALREADY_ORDER_CUSTOMERS = 7;
    //未下单新用户
    const NONE_ORDER_CUSTOMERS = 8;

    /**
     * @param $key
     * @return array
     */
    public static function getMountedCustomers($key)
    {
        $prefix = get_called_class();
        $redis = Tools::getRedis();
        //随机 rand_key_1 保存bit 0 0，用于 rand_key_1 or key ,复制key中的值
        $rand_key_1 = $prefix . '_' . microtime(true) . rand(0, 100000);
        $redis->setBit($rand_key_1, 0, 0);

        //临时随机 rand_key_2 保存key的副本，rand_key_1 or key得到
        $rand_key_2 = $prefix . '_' . microtime(true) . rand(0, 100000);
        $redis->bitOp('OR', $rand_key_2, $key, $rand_key_1);

        //得到rand_key_2位为1的所有偏移量
        $customer_ids = [];
        while (true) {
            $index = $redis->bitpos($rand_key_2, 1);
            if ($index < 0) {
                break;
            }
            $customer_ids[] = $index;
            $redis->setBit($rand_key_2, $index, 0);
        }
        //删除临时key
        $redis->del($rand_key_1, $rand_key_2);
        return $customer_ids;
    }

    /**
     * @param $key
     * @return array
     * 从redis拿出字符串，转换成二进制进行遍历
     * 遍历时12个字符为一组，提高效率
     */
    public static function getMountedCustomersPro($key){
        $redis = Tools::getRedis();
        Tools::log('customer_count:'.$redis->bitCount($key),'wangyang.log');
        $hexString = $redis->get($key);//128w,320k
        $bytes = unpack("H*", $hexString);
        $bytes = $bytes[1];
        $length = strlen($bytes);
        $byteCount = 0;
        $offset = 0;
        $bulk = 12;
        $ids = [];
        while ($offset < $length) {
            $str = substr($bytes, $offset, $bulk);
            if (strlen($str) !== $bulk) {
                $bulk = strlen($str);
            }
            $dec = hexdec($str);
            $offset += $bulk;
            $size = $bulk * 4;
            if ($dec > 0) {
                $bin = decbin($dec);
                $bin = str_pad($bin, $size, '0', STR_PAD_LEFT);
//            echo $bin . PHP_EOL;
                $pos = 0;
                while ($pos < $size) {
                    $pos = strpos($bin, '1', $pos);
                    if ($pos === false) {
                        break;
                    } else {
                        $ids[] = $byteCount + $pos++;
                    }
                }
            }
            $byteCount += $size;
        }
        return $ids;
    }

    /**
     * @param $tag_id
     * @return array
     * 标签当前挂载的用户
     */
    public static function getTagCurrentMountedCustomers($tag_id){
        $key = self::getCurrentTagKey($tag_id);
        return self::getMountedCustomersPro($key);
    }

    /**
     * @param $customer_id
     * @return array
     * 用户当前的标签
     */
    public static function getCustomerTags($customer_id)
    {
        $redis = Tools::getRedis();
        //用户标签
        $tag_all = DimensionTag::find()->joinWith('dimension')->asArray()->all();
        $tags = [];
        foreach ($tag_all as $tag) {
            $key = Tag::getCurrentTagKey($tag['entity_id']);
            //redis中取出标签
            if ($redis->exists($key)) {
                $flag = $redis->getBit($key, $customer_id);
                if ($flag == 1) {
                    $tags[$tag['dimension_id']]['dimension_name'] = $tag['dimension']['name'];
                    $tags[$tag['dimension_id']]['tags'][] = $tag['name'];
                }
            }
        }

        return array_values($tags);
    }

    /**
     * @param $customer_id
     * @return array
     * 用户当前的标签
     */
    public static function getCustomerTagsArray($customer_id)
    {
        $redis = Tools::getRedis();
        //用户标签
        $tag_all = DimensionTag::find()->joinWith('dimension')->asArray()->all();
        $tags = [];
        foreach ($tag_all as $tag) {
            $key = Tag::getCurrentTagKey($tag['entity_id']);
            //redis中取出标签
            if ($redis->exists($key)) {
                $flag = $redis->getBit($key, $customer_id);
                if ($flag == 1) {
                    array_push($tags,$tag['entity_id']);
                }
            }
        }

        return $tags;
    }

    /**
     * @return array
     * 用户当前的标签
     */
    public static function getAllTagsArray()
    {
        //用户标签
        $tag_all = DimensionTag::find()->joinWith('dimension')->where(['status' => 1])->asArray()->all();
        $tags = [];
        foreach ($tag_all as $tag) {
            $tags[$tag['entity_id']] = $tag['name'];
        }
        return $tags;
    }

    public static function getCount($key)
    {
        $redis = Tools::getRedis();
        return $redis->bitCount($key);
    }

    /**
     * @param $tag_id
     * @param $customer_ids
     * @param boolean $reset  是否重置该标签
     * @return bool
     * @throws \Exception 批量挂载人群
     */
    public static function mountCustomer($tag_id, $customer_ids, $reset = false)
    {
        $key = self::getCurrentTagKey($tag_id);

        $redis = Tools::getRedis();

        if($reset){
            //初始化这个key
            $redis->set($key,"\x00");
        }
        if (!$tag_id || !is_array($customer_ids) || count($customer_ids) == 0) {
            return false;
        }

        /** @var \Redis $pipe */
        //批量获取
        $pipe = $redis->multi(\Redis::PIPELINE);
        foreach ($customer_ids as $customer_id) {
            $pipe->setBit($key, $customer_id, 1);
        }

        $pipe->exec();

    }

    /**
     * @param $tag_id
     * @param $customer_ids
     * @param $date
     * @return bool
     * @throws \Exception 历史挂载人群
     */
    public static function mountHistoryCustomer($tag_id, $customer_ids, $date)
    {
        $key = self::getHistoryTagKey($tag_id, $date);

        if (!$tag_id || !is_array($customer_ids) || count($customer_ids) == 0 || !$date) {
            return false;
        }

        $redis = Tools::getRedis();
        $redis->del($key);

        /** @var \Redis $pipe */
        //批量设置
        $pipe = $redis->multi(\Redis::PIPELINE);
        foreach ($customer_ids as $customer_id) {
            $pipe->setBit($key, $customer_id, 1);
        }
        $pipe->exec();
    }

    /**
     * @param $tag_id
     * @param $customer_ids
     * @throws \Exception
     * 批量删除挂载人群
     */
    public static function unMountCustomer($tag_id, $customer_ids)
    {
        if (!$tag_id || !is_array($customer_ids) || count($customer_ids) == 0) {
            throw new \Exception('参数错误', 500);
        }

        $key = self::getCurrentTagKey($tag_id);

        $redis = Tools::getRedis();
        /** @var \Redis $pipe */
        //批量获取
        $pipe = $redis->multi(\Redis::PIPELINE);
        foreach ($customer_ids as $customer_id) {
            $pipe->setBit($key, $customer_id, 0);
        }
        $pipe->exec();
    }

    public static function getHistoryTagKey($tag_id, $date)
    {
        return Tag::AUTO_HISTORY_TAG_SUFFIX . $tag_id . '_' . $date;
    }

    public static function getCurrentTagKey($tag_id)
    {
        return Tag::CUSTOMER_CURRENT_TAG_SUFFIX . $tag_id;
    }

}