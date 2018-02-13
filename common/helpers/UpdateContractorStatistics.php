<?php
/**
 * Created by PhpStorm.
 * User: Ryan Hong
 * Date: 2017/11/2
 * Time: 15:31
 */

namespace common\helpers;

use common\models\contractor\ContractorStatisticsData;
use framework\components\ToolsAbstract;

/**
 * Class UpdateContractorStatistics
 * @package common\helpers
 */
class UpdateContractorStatistics
{
    public static function run($body)
    {
        //只能普通超市在普通供应商下的单才会被统计
        $customer_tag_id = isset($body['customer_tag_id']) ? $body['customer_tag_id'] : 1;
        $merchant_type_id = isset($body['merchant_type_id']) ? $body['merchant_type_id'] : 1;
        if ($customer_tag_id == 1 && $merchant_type_id == 1) {
            $grand_total = $body['grand_total'];
            $first_order = $body['is_first_order'];
            $city = $body['city'];
            $contractor_id = $body['contractor_id'];
            //utc加8个小时，转成prc时间
            $date = date('Y-m-d', strtotime('+8 hours', strtotime($body['created_at'])));
            /** @var ContractorStatisticsData $contractor_statistics_data */
            $contractor_statistics_data = ContractorStatisticsData::find()
                ->where(['city' => $city, 'date' => $date, 'contractor_id' => $contractor_id])->one();
            if (!$contractor_statistics_data) {
                $contractor_statistics_data = new ContractorStatisticsData();
                $contractor_statistics_data->city = $city;
                $contractor_statistics_data->date = $date;
                $contractor_statistics_data->contractor_id = $contractor_id;
            }
            $contractor_statistics_data->sales_total += $grand_total;
            if ($first_order == 1) {
                $contractor_statistics_data->first_users += 1;
            }

            $contractor_statistics_data->orders_count += 1;
            $contractor_statistics_data->save();
            ToolsAbstract::log($contractor_statistics_data->errors, 'onMQProcess.log');
        }
    }
}