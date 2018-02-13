<?php
/**
 * 供货商综合得分规则
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 11:22
 */

namespace service\tasks\contractor;

use framework\components\Date;
use framework\core\ServiceAbstract;
use yii\db\Expression;
use common\models\contractor\ContractorTasks;
use common\components\UserTools;
use framework\db\readonly\models\core\SalesFlatOrder;
use common\models\contractor\ContractorMetrics;
use common\models\contractor\ContractorTaskHistory;
use framework\components\ToolsAbstract;
use service\tasks\TaskService;

class firstOrderCustomer extends TaskService
{
    public function run($data)
    {
        $date = new Date();
        //更新包括今天在内的最近9天
        $today = $date->date("Y-m-d");
        //ToolsAbstract::log($today,'aaa.log');
        $start_date = date("Y-m-d", strtotime('-8 day', $date->timestamp()));//8天前

        //获取指标id
        $metric_id = ContractorMetrics::getMetricIdByIdentifier(ContractorMetrics::METRIC_IDENTIFIER_FIRST_ORDER_CUSTOMER_COUNT);
        if (!$metric_id) {
            $this->log('invalid metric_identifier');
            return;
        }

        //获取9天内，首单用户数分日分业务员累计数据（包括业务员为空的）
        $data = $this->getFirstOrderCustomerCount($start_date);

        //先把9天内的数据都更新成0
        ContractorTaskHistory::updateAll(['value' => 0],"metric_id= $metric_id and date >= '$start_date'");

//        $format_data_contractor = [];
        $format_data_city = [];
        foreach ($data as $item) {
            $this->updateHistory($metric_id, $item['contractor_id'], $item['city'], $item['date'], $item['count']);
//            if ($item['city'] && $item['contractor_id']) {
//                $key = $item['city'] . '_' . $item['contractor_id'] . '_' . $item['date'];
//                $format_data_contractor[$key] = $item['count'];
//            }

            if ($item['city']) {
                $key = $item['city'] . '_' . $item['date'];
                if (isset($format_data_city[$key])) {
                    $format_data_city[$key]['value'] += $item['count'];
                } else {
                    $format_data_city[$key] = [
                        'value' => $item['count'],
                        'city' => $item['city'],
                        'date' => $item['date']
                    ];
                }
            }
        }

        foreach ($format_data_city as $item){
            $this->updateHistory($metric_id, 0, $item['city'], $item['date'], $item['value']);
        }

        //获取分配了任务的业务员和城市
//        $month = intval($date->date("Ym"));
//        $last_month = intval(date("Ym", strtotime("-1 month", $date->timestamp())));
//        if (intval(date("Ym", strtotime($start_date))) == $last_month) {//跨月了，一部分日期是上个月的
//            $task_query = ContractorTasks::find()
//                ->where(['metric_id' => $metric_id])
//                ->andWhere(['month' => $last_month])
//                ->asArray()->all();
//
//            foreach ($task_query as $item) {
//                $_manage_day = $start_date;
//                if ($item['owner_type'] == 1) {//城市的
//                    while (intval(date("Ym", strtotime($_manage_day))) == $last_month) {
//                        $key = $item['city'] . '_' . $_manage_day;
//                        $value = isset($format_data_city[$key]) ? $format_data_city[$key] : 0;
//                        $this->updateHistory($metric_id, 0, $item['city'], $_manage_day, $value);
//
//                        $_manage_day = date("Y-m-d", (strtotime($_manage_day) + 24 * 3600));
//                    }
//                } elseif ($item['owner_type'] == 2) {//业务员的
//                    while (intval(date("Ym", strtotime($_manage_day))) == $last_month) {
//                        $key = $item['city'] . '_' . $item['owner_id'] . '_' . $_manage_day;
//                        $value = isset($format_data_contractor[$key]) ? $format_data_contractor[$key] : 0;
//                        $this->updateHistory($metric_id, $item['owner_id'], $item['city'], $_manage_day, $value);
//
//                        $_manage_day = date("Y-m-d", (strtotime($_manage_day) + 24 * 3600));
//                    }
//                }
//            }
//        }

        //本月的任务
//        $task_query = ContractorTasks::find()
//            ->where(['metric_id' => $metric_id])
//            ->andWhere(['month' => $month])
//            ->asArray()->all();
//
//        //如果跨月了，本月的从1号开始，如果没跨月，本月从start_date开始
//        $current_month_start_date = intval(date("Ym", strtotime($start_date))) == $last_month ? $date->date("Y-m-01") : $start_date;
//        foreach ($task_query as $item) {
//            $_manage_day = $current_month_start_date;
//            if ($item['owner_type'] == 1) {//城市的
//                while (strtotime($_manage_day) <= strtotime($today)) {
//                    $key = $item['city'] . '_' . $_manage_day;
//                    $value = isset($format_data_city[$key]) ? $format_data_city[$key] : 0;
//                    $this->updateHistory($metric_id, 0, $item['city'], $_manage_day, $value);
//
//                    $_manage_day = date("Y-m-d", (strtotime($_manage_day) + 24 * 3600));
//                }
//            } elseif ($item['owner_type'] == 2) {//业务员的
//                while (strtotime($_manage_day) <= strtotime($today)) {
//                    $key = $item['city'] . '_' . $item['owner_id'] . '_' . $_manage_day;
//                    $value = isset($format_data_contractor[$key]) ? $format_data_contractor[$key] : 0;
//                    $this->updateHistory($metric_id, $item['owner_id'], $item['city'], $_manage_day, $value);
//
//                    $_manage_day = date("Y-m-d", (strtotime($_manage_day) + 24 * 3600));
//                }
//            }
//        }

        $this->log('task finished');
    }

    private function updateHistory($metric_id, $owner_id, $city, $date, $value)
    {
        ToolsAbstract::log("$owner_id - $city - $date - $value",'aaa.log');
        $history = ContractorTaskHistory::findOne(['metric_id' => $metric_id, 'owner_id' => $owner_id, 'city' => $city, 'date' => $date]);
        if (!$history) {
            $history = new ContractorTaskHistory();
        }

        $date_obj = new Date();
        $history->metric_id = $metric_id;
        $history->owner_id = $owner_id;
        $history->city = $city;
        $history->date = $date;
        $history->value = $value;
        $history->updated_at = $date_obj->date();
        $history->save();
        if (!empty($history->getErrors())) {
            $this->log($history->getErrors());
        }
    }

    private function getFirstOrderCustomerCount($start_date, $end_date = null)
    {
        //子查询查出所有首单的订单id
        $subQuery = SalesFlatOrder::find()->select([new Expression("cast(SUBSTRING_INDEX(group_concat(entity_id order by `created_at` asc),',',1) as signed) as entity_id")])
            ->where(['customer_tag_id' => [SalesFlatOrder::CUSTOMER_TAG_ID_NORMAL, SalesFlatOrder::CUSTOMER_TAG_ID_3C]])
            ->andWhere(['not in', 'state', SalesFlatOrder::INVALID_ORDER_STATE()])
            ->andWhere(['not in', 'wholesaler_id', SalesFlatOrder::excludeWholesalerIds()])
            ->andWhere(['not in', 'customer_id', SalesFlatOrder::excludeCustomerIds()])
            ->andWhere(['not like', 'wholesaler_name', ['t', 'T', '特通渠道', '乐来供应链', '测试']])
            ->groupBy('customer_id');

        //查出首单的下单日期、业务员和城市
        $subQuery2 = SalesFlatOrder::find()->select([new Expression("substring(addtime(created_at,'8:00:00'),1,10) as date"), 'contractor_id', 'city'])
            ->where(['in', 'entity_id', $subQuery]);

        $query = SalesFlatOrder::find()->select([new Expression("count(*) as count"), 'date', 'contractor_id', 'city'])
            ->from(['a' => $subQuery2])->where(['>=', 'date', $start_date]);
        if ($end_date) {
            $query->andWhere(['<=', 'date', $end_date]);
        }
        $query->groupBy(['date', 'contractor_id', 'city']);
        ToolsAbstract::log($query->createCommand()->rawSql,'aaa.log');

        $data = $query->asArray()->all();
        return $data;
    }

}