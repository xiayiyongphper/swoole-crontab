<?php
/**
 * Created by PhpStorm.
 * User: Ryan Hong
 * Date: 2017/11/2
 * Time: 12:02
 */

namespace common\helpers;

use common\components\UserTools;
use common\models\contractor\ContractorMetrics;
use common\models\contractor\ContractorTaskHistory;
use framework\components\Date;
use framework\components\ToolsAbstract;
use framework\db\readonly\models\core\SalesFlatOrder;
use framework\mq\MQAbstract;

/**
 * Class UpdateContractorTaskHistory
 * @package common\helpers
 */
class UpdateContractorTaskHistory
{
    const ACTION_ORDER_CREATE = 1;
    const ACTION_ORDER_CANCEL = 2;

    /**
     * @deprecated contractor gmv update bug on 2017-11-16 16:55
     * @param $data
     * @param $action
     */
    public static function updateGmvStat($data, $action)
    {
        return false;
        $gmv_metric_id = ContractorMetrics::getMetricIdByIdentifier(ContractorMetrics::METRIC_IDENTIFIER_MONTH_ORDER_GMV);
        $contractor_id = $data['contractor_id'];
        $city = $data['city'];
        $subtotal = $data['subtotal'];
        $date_obj = new Date();
        $date = $date_obj->date("Y-m-d", $data['created_at']);//创建订单的日期
        ToolsAbstract::log('gmv', 'onMQProcess.log');
        ToolsAbstract::log($subtotal, 'onMQProcess.log');
        ToolsAbstract::log($city, 'onMQProcess.log');
        ToolsAbstract::log('gmv', 'onMQProcess.log');
        switch ($action) {
            case  MQAbstract::MSG_ORDER_NEW:
                self::updateTaskHistoryValue($date, $gmv_metric_id, $contractor_id, $city, $subtotal);
                self::updateTaskHistoryValue($date, $gmv_metric_id, 0, $city, $subtotal);
                break;
            case MQAbstract::MSG_ORDER_CANCEL:
            case MQAbstract::MSG_ORDER_CLOSED:
            case MQAbstract::MSG_ORDER_AGREE_CANCEL:
            case MQAbstract::MSG_ORDER_REJECTED_CLOSED:
                self::updateTaskHistoryValue($date, $gmv_metric_id, $contractor_id, $city, -$subtotal);
                self::updateTaskHistoryValue($date, $gmv_metric_id, 0, $city, -$subtotal);
                break;
            default:
                break;
        }
    }

    //更新业务员任务记录中，首单用户数和月下单用户数两个指标

    /**
     * @deprecated contractor gmv update bug on 2017-11-16 16:55
     * @param $data
     */
    public static function updateOrderCustomerCount($data)
    {
        return false;
        $customer_id = $data['customer_id'];
        $wholesaler_id = $data['wholesaler_id'];
        $order_id = $data['entity_id'];
        $contractor_id = $data['contractor_id'];
        $city = $data['city'];
        $status = $data['state'];
        //$exclude_customer = [1021,1206,1208,1215,1245,2299,2376,2476,1942,1650,2541];//要排除的用户id
        //$exclude_customer = [0];
        $exclude_customer = SalesFlatOrder::excludeCustomerIds();
        $exclude_wholesaler = SalesFlatOrder::excludeWholesalerIds();

        if (isset($data['customer_tag_id']) && $data['customer_tag_id'] != 1) {
            return;
        }
        if (!$customer_id || in_array($customer_id, $exclude_customer)) {
            return;
        }
        if (in_array($wholesaler_id, $exclude_wholesaler)) {
            return;
        }

        $action = in_array($status, SalesFlatOrder::VALID_ORDER_STATUS()) ? self::ACTION_ORDER_CREATE : self::ACTION_ORDER_CANCEL;
        $date_obj = new Date();
        $date = $date_obj->date("Y-m-d", $data['created_at']);//创建订单的日期

        //---------------------更新首单用户数---------------------------
        $first_order = SalesFlatOrder::getCustomerFirstOrder($customer_id);
        $first_order_metric_id = ContractorMetrics::getMetricIdByIdentifier(ContractorMetrics::METRIC_IDENTIFIER_FIRST_ORDER_CUSTOMER_COUNT);//任务维度id
        //创建订单
        if ($action == self::ACTION_ORDER_CREATE && !empty($first_order['entity_id']) && $first_order['entity_id'] == $order_id) {//此订单是该用户首单，更新统计数据
            //更新业务员任务记录
            if ($contractor_id) {
                self::updateTaskHistoryValue($date, $first_order_metric_id, $contractor_id, $city, 1);
            }
            //更新城市任务记录
            self::updateTaskHistoryValue($date, $first_order_metric_id, 0, $city, 1);

        }

        //取消订单
        if ($action == self::ACTION_ORDER_CANCEL) {
            if (empty($first_order['entity_id'])) {//没有首单，那么这个订单原来是首单，而且用户只有这一个订单
                //订单创建日，业务员和城市的任务记录要减1
                if ($contractor_id) {
                    self::updateTaskHistoryValue($date, $first_order_metric_id, $contractor_id, $city, -1);
                }
                self::updateTaskHistoryValue($date, $first_order_metric_id, 0, $city, -1);
            } elseif (!empty($first_order['created_at']) && strtotime($first_order['created_at']) > $date_obj->timestamp($data['created_at'])) {
                //如果存在首单，且首单的创建时间晚于当前订单，说明当前订单原来是首单
                $first_order_date = substr($first_order['created_at'], 0, 10);//首单的下单日期

                //订单创建日，业务员和城市的任务记录要减1
                if ($contractor_id) {
                    self::updateTaskHistoryValue($date, $first_order_metric_id, $contractor_id, $city, -1);
                }
                self::updateTaskHistoryValue($date, $first_order_metric_id, 0, $city, -1);

                //新首单的创建日，业务员和城市的任务记录要加1
                if ($contractor_id) {
                    self::updateTaskHistoryValue($first_order_date, $first_order_metric_id, $contractor_id, $city, 1);
                }
                self::updateTaskHistoryValue($first_order_date, $first_order_metric_id, 0, $city, 1);
            }
        }

        //-------------------------更新月下单用户数------------------------------
        $start_date = $date_obj->date("Y-m-01", $data['created_at']);//下单月份1号
        $end_date = date("Y-m-d", strtotime("+1 month", strtotime($start_date)) - 1);//下单月份最后一天

        $metric_id = ContractorMetrics::getMetricIdByIdentifier(ContractorMetrics::METRIC_IDENTIFIER_MONTH_ORDER_CUSTOMER_COUNT);//任务维度id

        //更新业务员任务记录
        if ($contractor_id) {
            //获取该用户在该业务员处的本月首单
            $month_first_order = SalesFlatOrder::getCustomerFirstOrder($customer_id, $start_date, $end_date, $contractor_id, $city);

            if ($action == self::ACTION_ORDER_CREATE && !empty($month_first_order['entity_id']) && $month_first_order['entity_id'] == $order_id) {
                //此订单是该用户在该业务员处本月首单，更新统计数据
                self::updateTaskHistoryValue($date, $metric_id, $contractor_id, $city, 1);
            }

            if ($action == self::ACTION_ORDER_CANCEL) {
                if (empty($month_first_order['entity_id'])) {//没有新首单，那么这个订单原来是首单，而且用户只有这一个订单
                    //订单创建日，业务员任务减1
                    self::updateTaskHistoryValue($date, $metric_id, $contractor_id, $city, -1);
                } elseif (!empty($month_first_order['created_at']) && strtotime($month_first_order['created_at']) > $date_obj->timestamp($data['created_at'])) {
                    //新首单的创建时间晚于当前订单，说明当前订单原来是首单
                    self::updateTaskHistoryValue($date, $metric_id, $contractor_id, $city, -1);
                    $first_order_date = substr($month_first_order['created_at'], 0, 10);//新首单的下单日期
                    self::updateTaskHistoryValue($first_order_date, $metric_id, $contractor_id, $city, 1);
                }
            }
        }

        //更新城市任务记录
        $month_first_order = SalesFlatOrder::getCustomerFirstOrder($customer_id, $start_date, $end_date, null, $city);//获取该用户在该城市的本月首单
        if ($action == self::ACTION_ORDER_CREATE && !empty($month_first_order['entity_id']) && $month_first_order['entity_id'] == $order_id) {
            //此订单是该用户在该城市本月首单，更新统计数据
            self::updateTaskHistoryValue($date, $metric_id, 0, $city, 1);
        }

        if ($action == self::ACTION_ORDER_CANCEL) {
            if (empty($month_first_order['entity_id'])) {//没有新首单，那么这个订单原来是首单，而且用户只有这一个订单
                //订单创建日，业务员任务减1
                self::updateTaskHistoryValue($date, $metric_id, 0, $city, -1);
            } elseif (!empty($month_first_order['created_at']) && strtotime($month_first_order['created_at']) > $date_obj->timestamp($data['created_at'])) {
                //新首单的创建时间晚于当前订单，说明当前订单原来是首单
                self::updateTaskHistoryValue($date, $metric_id, 0, $city, -1);
                $first_order_date = substr($month_first_order['created_at'], 0, 10);//新首单的下单日期
                self::updateTaskHistoryValue($first_order_date, $metric_id, 0, $city, 1);
            }
        }


        ToolsAbstract::log("------------update end----------------", 'observer.log');
    }

    //更新业务员任务记录中，订单数指标

    /**
     * @deprecated contractor gmv update bug on 2017-11-16 16:55
     * @param $data
     * @param $action
     */
    public static function updateOrderCount($data, $action)
    {
        return false;
        try {
            ToolsAbstract::log('updateOrderCountStatisticsFromMQ-start', 'onMQProcess.log');
            $metric_id = ContractorMetrics::getMetricIdByIdentifier(ContractorMetrics::ID_ORDER_COUNT);
            $contractor_id = $data['contractor_id'];
            $orderId = $data['entity_id'];
            $incrementId = $data['increment_id'];
            $city = $data['city'];
            $date_obj = new Date();
            $date = $date_obj->date("Y-m-d", $data['created_at']);//创建订单的日期
            ToolsAbstract::log($orderId, 'onMQProcess.log');
            ToolsAbstract::log($incrementId, 'onMQProcess.log');
            ToolsAbstract::log($metric_id, 'onMQProcess.log');
            ToolsAbstract::log($contractor_id, 'onMQProcess.log');
            ToolsAbstract::log($city, 'onMQProcess.log');
            ToolsAbstract::log($date, 'onMQProcess.log');
            ToolsAbstract::log($action, 'onMQProcess.log');
            switch ($action) {
                case  MQAbstract::MSG_ORDER_NEW:
                    self::updateTaskHistoryValue($date, $metric_id, $contractor_id, $city, 1);
                    self::updateTaskHistoryValue($date, $metric_id, 0, $city, 1);
                    break;
                case MQAbstract::MSG_ORDER_CANCEL:
                case MQAbstract::MSG_ORDER_CLOSED:
                case MQAbstract::MSG_ORDER_AGREE_CANCEL:
                case MQAbstract::MSG_ORDER_REJECTED_CLOSED:
                    self::updateTaskHistoryValue($date, $metric_id, $contractor_id, $city, -1);
                    self::updateTaskHistoryValue($date, $metric_id, 0, $city, -1);
                    break;
                default:
                    break;
            }
            ToolsAbstract::log('updateOrderCountStatisticsFromMQ-finish', 'onMQProcess.log');
        } catch (\Exception $e) {
            ToolsAbstract::logException($e);
            ToolsAbstract::log('updateOrderCountStatisticsFromMQ-exception', 'onMQProcess.log');
        }
    }

    /**
     * 改变一条任务记录的值
     * @param $date //日期
     * @param $metric_id //维度id
     * @param $owner_id //业务员id
     * @param $city //城市
     * @param $added_value //增加的值，如果是减少则为负数
     * @return boolean
     */
    private static function updateTaskHistoryValue($date, $metric_id, $owner_id, $city, $added_value)
    {
        $date_obj = new Date();
        $task_history = ContractorTaskHistory::findOne([
            'date' => $date,
            'metric_id' => $metric_id,
            'owner_id' => $owner_id,
            'city' => $city
        ]);

        if ($task_history) {
            //ToolsAbstract::log($task_history,'observer.log');
            ToolsAbstract::log($added_value, 'observer.log');
            $task_history->value = floatval($task_history->value) + $added_value;
            $task_history->updated_at = $date_obj->date();
            $task_history->save();
            if (!empty($task_history->getErrors())) {
                ToolsAbstract::log($task_history->getErrors(), 'exception.log');
                return false;
            }
        }

        return true;
    }
    
}