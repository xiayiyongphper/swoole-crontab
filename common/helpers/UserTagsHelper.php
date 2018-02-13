<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/11
 * Time: 18:25
 */

namespace common\helpers;

use framework\components\ToolsAbstract;

/**
 * Class UserTagsHelper
 * @package common\helpers
 */
class UserTagsHelper
{

    /**
     * @param int[] $tagIds
     * @return array
     */
    public static function getUserIdsByTagIds($tagIds)
    {
        if (!$ch = curl_init()) {
            return [];
        }

        curl_setopt($ch, CURLOPT_URL, self::getUrlPrefix() . 'get-customer-ids-by-tag-ids');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['tag_ids' => $tagIds]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
            'Authorization:Bearer ' . self::getAuthToken()
        ]);

        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        if (!$result || empty($result['data']['customer_ids']) || !is_array($result['data']['customer_ids'])) {
            return [];
        }
        return $result['data']['customer_ids'];
    }

    /**
     * @param int[] $tagIds
     * @return array
     */
    public static function getIsCustomerIdInTagIds($customerId, $tagIds)
    {
        if (!$ch = curl_init()) {
            return [];
        }

        curl_setopt($ch, CURLOPT_URL, self::getUrlPrefix() . 'get-is-customer-id-in-tag-ids');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['customer_id' => $customerId, 'tag_ids' => $tagIds]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
            'Authorization:Bearer ' . self::getAuthToken()
        ]);

        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        ToolsAbstract::log($result, 'UserTagsHelper.log');
        if (!$result || empty($result['data']['result'])) {
            return false;
        }
        return true;
    }

    private static function isRelease()
    {
        $version = defined('ENV_NODE_VERSION') ? ENV_NODE_VERSION : '';
        return $version === 'release';
    }

    private static function getUrlPrefix()
    {
        $domain = self::isRelease() ? 'api-group.lelai.com' : 'api-group.laile.com';
        $prefix = $domain . '/customers/customer/';
        return $prefix;
    }

    private static function getAuthToken()
    {
        $token = defined('ENV_GROUP_AUTH_TOKEN') ? ENV_GROUP_AUTH_TOKEN : 'l7DnSu_pj1_5OcvuqHpe2-XLi6oqeJNtpp0pyTpe';
        return $token;
    }
}