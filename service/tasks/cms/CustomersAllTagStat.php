<?php

namespace service\tasks\cms;


use common\helpers\Tag;
use common\helpers\Tools;
use common\models\DimensionTag;
use common\models\LeCustomers;
use common\models\LeLaiUserAllResult;
use common\models\RegionArea;
use service\tasks\TaskService;

class CustomersAllTagStat extends TaskService
{
    /**
     * 每天早上4:30运行，统计前一天的数据
     * 统计维度为 用户活跃度(自动)、特别活跃用户,零食用户
     * @param mixed $data
     * @return mixed|void
     */
    public function run($data)
    {
        //各个标签的统计数据
        $current_history_tag_stat = [];
        //不同区域内所有用户
        $area_customers_all = [];

        $timeStamp = Tools::getDate()->timestamp();
        $yesterday = date('Y-m-d', strtotime('-1 day', $timeStamp));
        $today = Tools::getDate()->date('Y-m-d');

        //统计每个区域的信息
        $regions = RegionArea::find()->all();
        /** @var RegionArea $region */
        foreach ($regions as $region) {
            //区域内所有用户（基数）
            $area_customers_all[$region->entity_id] = LeCustomers::find()
                ->select('entity_id')
                ->andWhere(['area_id' => $region->entity_id])
                ->andWhere(['status' => LeCustomers::STATUS_NORMAL])
                ->column();  //审核通过用户
        }
        //所有自动标签
        $dimensionTags = DimensionTag::find()->select(['entity_id', 'name', 'dimension_id'])
            ->where(['dimension_id' => [1, 3, 4], 'status' => 1])->all();

        /** @var DimensionTag $dimensionTag */
        foreach ($dimensionTags as $dimensionTag) {
            $entity_id = $dimensionTag->entity_id;
            $name = $dimensionTag->name;
            $customerAll = [];
            switch ($dimensionTag->dimension_id) {
                case 1:  //用户活跃度(自动)
                    $customerAll = LeLaiUserAllResult::find()->select('user_id')->where(['user_type' => $name,
                        'imp_date' => $yesterday])->column();
                    break;
                case 3:  //自营零食用户活跃度(自动)
                    $customerAll = LeLaiUserAllResult::find()->select('user_id')->where(['user_socktype' => $name,
                        'imp_date' => $yesterday])->column();
                    break;
                case 4:  //自营零食意向用户(自动)
                    $customerAll = LeLaiUserAllResult::find()->select('user_id')->where(['user_tosick' => $name,
                        'imp_date' => $yesterday])->column();
                    break;
                default:
                    break;
            }

            Tools::log($yesterday, 'CustomersAllTagStat.log');
            Tools::log($today . ':' . count($customerAll), 'CustomersAllTagStat.log');

            Tag::mountHistoryCustomer($entity_id, $customerAll, $today);
            Tag::mountCustomer($entity_id, $customerAll, true);

            foreach ($area_customers_all as $area_id => $area_customers) {
                //与所有活跃上升用户做交集
                $customers_count = count(array_intersect($area_customers, $customerAll));
                $current_history_tag_stat[$entity_id]['count'][$area_id] = $customers_count;
                $current_history_tag_stat[$entity_id]['total'][$area_id] = count($area_customers);
            }
        }

        Tools::getRedis()->set(Tag::AUTO_HISTORY_TAG_STAT_SUFFIX . $today, json_encode($current_history_tag_stat));
    }
}