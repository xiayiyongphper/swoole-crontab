<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/10
 * Time: 16:38
 */

namespace common\helpers;

/**
 * Class Schema
 * @package common\helpers
 */
class Schema
{
    /**
     * 优惠券列表
     * @return string
     */
    public static function getCouponListSchema()
    {
        return 'lelaishop://coupon/list';
    }

    /**
     * 我的零钱
     * @return string
     */
    public static function getWalletSchema()
    {
        return 'lelaishop://page/wallet';
    }
}