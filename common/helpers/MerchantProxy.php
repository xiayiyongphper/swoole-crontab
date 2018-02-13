<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/11/2
 * Time: 16:04
 */

namespace common\helpers;


use framework\components\ProxyAbstract;
use framework\components\ToolsAbstract;
use service\message\common\Header;
use service\message\common\SourceEnum;
use service\message\core\AllSaleRuleRequest;
use service\message\core\AllSaleRuleResponse;
use service\message\merchant\reduceQtyRequest;
use service\message\sales\GreyListRequest;
use service\message\sales\GreyListResponse;
use service\message\sales\GreyListRule;

class MerchantProxy
{
    const LOG_FILE = 'MerchantProxy.log';

    const ROUTE_MERCHANT_GET_STORE_DETAIL = 'merchant.getStoreDetail';
    const ROUTE_SALES_GET_WHOLESALER = 'sales.getWholesaler';
    const ROUTE_SALES_ORDER_COUNT = 'sales.orderCountToday';
    const ROUTE_SALES_COUPON_RECEIVE_LIST = 'sales.couponReceiveList';
    const ROUTE_MERCHANT_GET_PRODUCT = 'merchant.getProduct';
    const ROUTE_MERCHANT_GET_RECENTLY_BUY_STORE = 'sales.getRecentlyBuyWholesalerIds';
    const ROUTE_SALES_RULE = 'sales.saleRule';
    const ROUTE_SALES_CUMULATIVE_RETURN_ACTIVITY = 'sales.GetCumulativeReturnDetail';
    const ROUTE_ALL_SALES_RULE = 'sales.allSaleRule';
    const ROUTE_SALES_GET_BLACK_GREY_LIST = 'sales.greyList';

    /**
     * @param $cities
     * @return array|bool
     * @throws \Exception
     */
    public static function getGreyList($rules)
    {
        $request = new GreyListRequest();
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $grey_list_rule = new GreyListRule();
                $grey_list_rule->setCity($rule['city']);
                $grey_list_rule->setDays($rule['days']);
                $grey_list_rule->setSeckillTimes($rule['seckill_times']);
                $request->appendRules($grey_list_rule);
            }
        }

        $header = new Header();
        $header->setSource(SourceEnum::CRONTAB);
        $header->setRoute(self::ROUTE_SALES_GET_BLACK_GREY_LIST);
        $message = ProxyAbstract::sendRequest($header, $request);

        if (!$message->getPackageBody()) {
            return false;
        }

        /** @var GreyListResponse $response */
        $response = new GreyListResponse();
        $response->parseFromString($message->getPackageBody());

        $grey_list = $response->getGreyList();
        foreach ($grey_list as $k => $item) {
            $grey_list[$k] = $item->toArray();
        }
        return $grey_list;
    }

    public static function reduceQty($products, $isFixGroupSubProducts = true)
    {
        try {
            $header = new Header();
            $header->setSource(SourceEnum::CRONTAB);
            $header->setVersion(1);
            $header->setRoute('merchant.reduceQty');

            ToolsAbstract::log($products, self::LOG_FILE);

            if (!$isFixGroupSubProducts) {
                $token = 'crontab_not_fix_group'; // 指定为crontab_not_fix_group，不修复套餐子商品
            } else {
                $token = 'test';
            }

            $request = new reduceQtyRequest();
            $request->setFrom([
                'customer_id' => 1,            // 必填参数，内网无需验证。
                'auth_token' => $token, // 必填参数，内网无需验证。
                'products' => $products,
            ]);
            ProxyAbstract::sendRequest($header, $request);
        } catch (\Exception $e) {
            ToolsAbstract::log($e->__toString(), self::LOG_FILE);
            ToolsAbstract::logException($e);
        }
    }

    /**
     * 用于统计综合得分脚本获取供应商全部优惠信息
     * @param int $wholesaler_id
     * @return bool|AllSaleRuleResponse
     * @throws \Exception
     */
    public static function getAllSaleRule($wholesaler_id = 0)
    {
        if (empty($wholesaler_id)) {
            return false;
        }

        if (!is_array($wholesaler_id)) {
            $wholesaler_id = [$wholesaler_id];
        }

        $request = new AllSaleRuleRequest();
        $data = [
            'wholesaler_id' => $wholesaler_id,
        ];

        $request->setFrom(ToolsAbstract::pb_array_filter($data));
        $header = new Header();
        $header->setSource(SourceEnum::MERCHANT);
        $header->setRoute(self::ROUTE_ALL_SALES_RULE);
        $message = ProxyAbstract::sendRequest($header, $request);;
        if (!$message->getPackageBody()) {
            return false;
        }
        /** @var AllSaleRuleResponse $response */
        $response = new AllSaleRuleResponse();
        $response->parseFromString($message->getPackageBody());

        return $response;
    }
}