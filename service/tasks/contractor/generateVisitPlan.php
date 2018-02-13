<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-5
 * Time: 上午10:38
 */

namespace service\tasks\contractor;

use common\helpers\Tools;
use common\models\common\AvailableCity;
use common\models\customer\PlanGroup;
use common\models\customer\VisitPlan;
use common\models\LeContractor;
use common\models\LelaiUserVisitScoreResult;
use service\tasks\TaskService;
use yii\helpers\ArrayHelper;

/**
 * Class generateVisitPlan
 * @package service\tasks\contractor
 * 每天执行一次，在calculateVisitScore之后执行
 *  * 0 2 * * *
 */
class generateVisitPlan extends TaskService
{

    public function run($data)
    {
        /** @var \yii\db\Connection $customerDb */
        $customerDb = \Yii::$app->customerDb;
        $city_all = AvailableCity::find()->all();
        $columns = [
            'customer_id', 'date', 'action', 'level',
        ];

        //删除三天的拜访计划
        $dateStart = Tools::getDate()->date('Y-m-d');

        for ($i = 0; $i < 3; $i++) {
            Tools::log('删除拜访计划：' . $dateStart, 'generateVisitPlan.log');
            VisitPlan::deleteAll(['action' => 0, 'date' => $dateStart]);
            $dateStart = date('Y-m-d', strtotime('+1 day', strtotime($dateStart)));
        }

        /** @var AvailableCity $city_one */
        foreach ($city_all as $city_one) {
            $city = $city_one->city_code;

            $contractors = LeContractor::find()->where(['city' => $city])->all();
            $planGroup = PlanGroup::find()->where(['city' => $city])->all();

            /** @var LeContractor $contractor */
            foreach ($contractors as $contractor) { //遍历该城市的业务员
                $result = [];
                /** @var PlanGroup $plan */
                foreach ($planGroup as $plan) {  //遍历该城市的拜访计划
                    //业务员在分组中所有超市，按分数排序
                    $visitScoreResult = LelaiUserVisitScoreResult::find()->select(['c.entity_id', 'fen_all'])->alias('r')
                        ->leftJoin('lelai_slim_customer.le_customers as c', 'r.entity_id = c.entity_id')
                        ->where(['group_id' => $plan->entity_id])
                        ->andWhere(['c.city' => $city])
                        ->andWhere(['c.contractor_id' => $contractor->entity_id])
                        ->orderBy('fen_all desc');

                    $visitScoreResult = $visitScoreResult->limit(30)->asArray()->all();
                    //分组超市小于15个，则不生成拜访计划
                    if (count($visitScoreResult) < 15) {
                        continue;
                    }

                    $scores = ArrayHelper::getColumn($visitScoreResult, 'fen_all');
                    $planCustomerIds = ArrayHelper::getColumn($visitScoreResult, 'entity_id');
                    $avgScore = array_sum($scores) / count($scores);

                    $pow_sum = 0;
                    foreach ($scores as $score) {
                        $pow_sum += pow($avgScore - $score, 2);
                    }
                    $variance = $pow_sum / count($scores);
                    $square = sqrt($variance);

                    $result[] = [
                        'plan_id' => $plan->entity_id,
                        'square' => $square,
                        'customer_ids' => $planCustomerIds
                    ];
                }

                //该计划下分数排序
                ArrayHelper::multisort($result, 'square', SORT_DESC);
                //取3个拜访计划
                $result = array_slice($result, 0, 3);

                $date = Tools::getDate()->date('Y-m-d');
                //插入到拜访记录里  记录3天的拜访计划
                foreach ($result as $planArray) {
                    Tools::log('业务员id:' . $contractor->entity_id, 'generateVisitPlan.log');
                    Tools::log('业务员名称:' . $contractor->name, 'generateVisitPlan.log');
                    Tools::log('plan_id:' . $planArray['plan_id'], 'generateVisitPlan.log');
                    Tools::log('方差:' . $planArray['square'], 'generateVisitPlan.log');

                    //手动添加或删除，不会覆盖
                    $manualPlanCustomer = VisitPlan::find()->select('customer_id')->where(['date' => $date])->column();

                    $planCustomerIds = $planArray['customer_ids'];
                    //前15个为必拜访
                    $needVisitData = [];
                    $needVisitCustomers = array_slice($planCustomerIds, 0, 15);
                    Tools::log('必定拜访', 'generateVisitPlan.log');
                    Tools::log($needVisitCustomers, 'generateVisitPlan.log');

                    foreach ($needVisitCustomers as $id) {
                        //判断是否手动操作过
                        if (in_array($id, $manualPlanCustomer)) {
                            continue;
                        }
                        $planData = [
                            $id, $date,
                            0, //自动生成
                            1 //必须拜访
                        ];
                        array_push($needVisitData, $planData);
                    }

                    if (empty($needVisitData)) {
                        continue;
                    }

                    $customerDb->createCommand()->batchInsert(VisitPlan::tableName(), $columns, $needVisitData)->execute();

                    // 后15个为可选拜访
                    $freeVisitData = [];
                    $freeVisitCustomers = array_slice($planCustomerIds, 15, 15);
                    Tools::log('可选拜访', 'generateVisitPlan.log');
                    Tools::log($freeVisitCustomers, 'generateVisitPlan.log');
                    foreach ($freeVisitCustomers as $id) {
                        //判断是否手动操作过
                        if (in_array($id, $manualPlanCustomer)) {
                            continue;
                        }
                        $planData = [
                            $id, $date,
                            0, //自动生成
                            2, //推荐拜访
                        ];
                        array_push($freeVisitData, $planData);
                    }

                    if (empty($freeVisitData)) {
                        continue;
                    }
                    $customerDb->createCommand()->batchInsert(VisitPlan::tableName(), $columns, $freeVisitData)->execute();
                    //三天的拜访计划
                    $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                }
            }
        }
    }
}