<?php
/**
 * 供货商综合得分规则
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 11:22
 */

namespace service\tasks\merchant;

use common\helpers\MerchantProxy;
use common\helpers\Tools;
use common\models\core\SalesFlatOrder;
use common\models\LeMerchantStore;
use common\models\Products;
use common\models\Tags;
use framework\components\ToolsAbstract;
use service\tasks\TaskService;
use yii\db\Expression;

class calculateScore extends TaskService
{
    const RULE_TAG_URL_PREFIX = "lelaishop://topicV3/list?rid=";
    public static $wholesalerCoupon = false;
    private $self_wholesaler_name = ['t', 'T', '特通渠道', '乐来供应链', '测试'];
    private $test_wholesaler_ids = [2, 4, 5, 12, 42, 260, 285];
    private $test_customer_id = [1021, 1206, 1208, 1215, 1245, 2299, 2376, 2476, 1942, 1650, 2541];
    private $calculate_status = ['pending', 'processing', 'processing_receive', 'processing_shipping', 'processing_arrived', 'pending_comment', 'complete'];

    private $reward_score = [100, 80, 60, 40, 20];

    //活动得分倍率
    const PROMOTION_RATE = 20;
    //特价商品得分倍率
    const SPECIAL_OFFER_RATE = 20;

    public function run($data)
    {
        ToolsAbstract::log(str_repeat('*--*', 32), 'debug.txt');
        $start_time = date("Y-m-d", strtotime("-1day"));
        $end_time = date("Y-m-d");
        //$end_time = date("Y-m-d", strtotime("+1day"));  //用于测试环境验证数据
        try {
            //查询前一天销售额前5名
            $saleTop5Score = $this->CalculateSale($start_time, $end_time);
            ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', $saleTop5Score:' . print_r($saleTop5Score, true) . PHP_EOL, 'debug.txt');
            //查询所有供货商列表
            $wholesalerIds = Tools::getAllWholesalerIds();

            //所有供货商信息[活动得分、特价商品得分]
            //$wholesalers = MerchantResourceAbstract::getStoreDetail2($wholesalerIds, 0);
            $wholesalers = $this->getStoreInfo($wholesalerIds);
            ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', $wholesalers:' . print_r($wholesalers, true) . PHP_EOL, 'debug.txt');

            //供货商权重得分
            $wholesalerScores = $this->getWholesalerScore($wholesalerIds);
            ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', $wholesalerScores:' . print_r($wholesalerScores, true) . PHP_EOL, 'debug.txt');
            //ToolsAbstract::log(str_repeat('-', 30), 'debugCalculate.txt');
            ToolsAbstract::log('|  供货商  |  供货商id  |  销售额得分   |   活动得分   |    特价商品得分  |   供货商权重分数  |  综合得分 |', 'debugCalculate.txt');
            ToolsAbstract::log('|--------|--------|--------|--------|--------|--------|--------|', 'debugCalculate.txt');

            //供货商综合得分 = 　销售额得分 + 活动得分 + 特价商品得分 + 供货商权重分数
            $compositeScore = [];
            foreach ($wholesalers as $k => $wholesaler) {
                $debugLogStr = "| ";  //记录log
                $debugLogStr .= $wholesaler['wholesaler_name'] . "  |";
                $wholesalerId = $wholesaler['wholesaler_id'];
                $debugLogStr .= " $wholesalerId  |";
                //销售额得分
                $totalScore = isset($saleTop5Score[$wholesalerId]) ? $saleTop5Score[$wholesalerId] : 0;
                $debugLogStr .= " $totalScore  |";
                //活动得分
                $promotionScore = 0;
                if (isset($wholesaler['promotion_message_in_tag']) && !empty($wholesaler['promotion_message_in_tag'])) {
                    $promotionScore = count($wholesaler['promotion_message_in_tag']) * self::PROMOTION_RATE;
                }
                $debugLogStr .= " $promotionScore(" . count($wholesaler['promotion_message_in_tag']) . ")  |";
                $totalScore += $promotionScore;
                //特价商品得分
                $specialOfferScore = 0;
                if (isset($wholesaler['special_product_number']) && !empty($wholesaler['special_product_number'])) {
                    $specialOfferScore = intval($wholesaler['special_product_number']) * self::SPECIAL_OFFER_RATE;
                }
                $debugLogStr .= " $specialOfferScore(" . intval($wholesaler['special_product_number']) . ")  |";
                $totalScore += $specialOfferScore;
                //供货商权重分数
                $scoreWeight = isset($wholesalerScores[$wholesalerId]) ? $wholesalerScores[$wholesalerId] : 0;
                $debugLogStr .= " $scoreWeight  |";
                $totalScore += $scoreWeight;

                $debugLogStr .= " $totalScore  |";
                ToolsAbstract::log($debugLogStr, 'debugCalculate.txt');
                //
                $compositeScore[$wholesalerId] = intval($totalScore);
            }
            ToolsAbstract::log(str_repeat('-', 30), 'debugCalculate.txt');
            unset($saleTop5Score, $wholesalerIds, $wholesalers, $wholesalerScores, $wholesaler);
            ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', $compositeScore:' . print_r($compositeScore, true) . PHP_EOL, 'debug.txt');
            if (empty($compositeScore)) {
                ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', exit by empty $compositeScore.', 'debug.txt');
                return false;
            }
        } catch (\Exception $e) {
            ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', throw Exception:' . $e->getMessage(), 'debug.txt');
        }

        //DB操作，事务
        $tr = LeMerchantStore::getDb()->beginTransaction();
        try {
            $table = LeMerchantStore::tableName();
            $sql = "UPDATE {$table} SET sort_score =0 WHERE sort_score>0;";
            LeMerchantStore::getDb()->createCommand($sql)->query();
            ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', sql:' . $sql . PHP_EOL, 'debug.txt');
            //
            $sql = $this->generateUpdateSQL($table, $compositeScore);
            LeMerchantStore::getDb()->createCommand($sql)->query();
            ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', sql:' . $sql . PHP_EOL, 'debug.txt');
            //提交
            $tr->commit();
        } catch (\Exception $e) {
            //回滚
            ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', rollBack.' . PHP_EOL, 'debug.txt');
            $tr->rollBack();
        }
        //print_r($compositeScore);
        return false;
    }


    /**
     * 查询前一天销售额前5的供货商
     * 有效单：前一天下单(订单创建时间)且未被取消或者拒单
     * @param $start_time
     * @param $end_time
     * @return array
     */
    private function CalculateSale($start_time, $end_time)
    {
        $sales = SalesFlatOrder::find()->select(['wholesaler_id', 'SUM(grand_total) as total_sale'])
            ->where(['between', 'created_at', $start_time, $end_time])
            ->andWhere(['not like', 'wholesaler_name', $this->self_wholesaler_name])
            ->andWhere(['not in', 'wholesaler_id', $this->test_wholesaler_ids])
            ->andWhere(['not in', 'customer_id', $this->test_customer_id])
            ->andWhere(['in', 'status', $this->calculate_status])
            ->groupBy('wholesaler_id')
            ->orderBy('total_sale desc');
        $sql = $sales->createCommand()->getRawSql();
        ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', $sql.' . $sql . PHP_EOL, 'debug.txt');
        $sales = $sales->asArray()->all();
        if (empty($sales)) {
            return [];
        }
        //
        ToolsAbstract::log('F:' . __FUNCTION__ . ', L' . __LINE__ . ', $sales:' . print_r($sales, true) . PHP_EOL, 'debug.txt');
        $level = count($this->reward_score);
        $total_sale = $sales[0]['total_sale'];
        $saleReward = [];
        $index = 0;
        ToolsAbstract::log(str_repeat('*--*', 30), 'debugCalculate.txt');
        ToolsAbstract::log('| 供货商id | 销售额 | 得分 |', 'debugCalculate.txt');
        ToolsAbstract::log('|--------|--------|--------|', 'debugCalculate.txt');
        foreach ($sales as $row) {
            if ($index >= $level) {
                break;
            }
            if ($row['total_sale'] < $total_sale) {
                $index++;
                $total_sale = $row['total_sale'];
            }
            if (!isset($this->reward_score[$index])) {
                break;
            }
            $saleReward[$row['wholesaler_id']] = $this->reward_score[$index];
            ToolsAbstract::log('| ' . $row['wholesaler_id'] . ' | ' . $row['total_sale'] . ' | ' . $this->reward_score[$index] . ' |', 'debugCalculate.txt');
        }
        ToolsAbstract::log(str_repeat('-', 30), 'debugCalculate.txt');
        return $saleReward;
    }


    /**
     * 供应商信息
     * @param $wholesalerIds
     * @return array|\framework\db\ActiveRecord[]
     */
    private function getWholesalersSimple($wholesalerIds)
    {
        //查出所有供应商
        $order = implode(',', $wholesalerIds);
        $order_by = [new Expression("FIELD (`entity_id`," . $order . ")")];     //按顺序查出所有供应商
        $wholesalers = LeMerchantStore::find()->select(['entity_id', 'sort'])->where(['in', 'entity_id', $wholesalerIds])
            ->orderBy($order_by)->asArray()->all();
        return $wholesalers;
    }


    /**
     * 获取供应商对应的权重得分
     * @param $wholesalerIds
     * @return array
     */
    private function getWholesalerScore($wholesalerIds)
    {
        $wholesalers_simple = $this->getWholesalersSimple($wholesalerIds);
        $wholesalerScores = [];
        foreach ($wholesalers_simple as $row) {
            $wholesalerScores[$row['entity_id']] = $row['sort'];
        }
        return $wholesalerScores;
    }

    /**
     * 批量更新sql
     * @param $table
     * @param $compositeScore
     * @return string
     */
    private function generateUpdateSQL($table, $compositeScore)
    {
        $clauseSql = "";
        foreach ($compositeScore as $entity_id => $sort_score) {
            $clauseSql .= " WHEN {$entity_id} THEN {$sort_score} ";
        }
        $wholesalerIds = implode(',', array_keys($compositeScore));
        $sql = "UPDATE `{$table}` SET `sort_score` = CASE `entity_id` {$clauseSql} END WHERE `entity_id` IN ({$wholesalerIds});";
        return $sql;
    }

    /**
     * 根据$storeModel返回商家详情数组
     * promotion_message_in_tag
     * special_product_number
     * @param $wholesalerIds
     * @param $areaId
     * @return array
     */
    private function getStoreInfo($wholesalerIds)
    {
        $data = [];
        if (!is_array($wholesalerIds) || count($wholesalerIds) == 0) {
            return $data;
        }

        //查出所有供应商
        $order = implode(',', $wholesalerIds);
        $order_by = [new Expression("FIELD (`entity_id`," . $order . ")")];     //按顺序查出所有供应商
        $wholesalers = LeMerchantStore::find()->where(['in', 'entity_id', $wholesalerIds])
            ->orderBy($order_by)->asArray()->all();

        //供应商促销信息[全部优惠]
        $rules = self::getWholesalerAllPromotions(array_unique($wholesalerIds));

        //组织数据
        foreach ($wholesalers as $merchantInfo) {
            $promotion_message_in_tag = self::getWholesalerPromotionMessageInTag($rules, $merchantInfo['entity_id']);
            $data[$merchantInfo['entity_id']] = [
                'wholesaler_id' => $merchantInfo['entity_id'],
                'wholesaler_name' => $merchantInfo['store_name'],
                'promotion_message_in_tag' => $promotion_message_in_tag,
                'special_product_number' => 0, //特价商品数量
            ];
            //
            $now = date("Y-m-d H:i:s");
            $model = new Products($merchantInfo['city']);
            //特价商品
            $product_ids = $model->find()
                ->where(['wholesaler_id' => $merchantInfo['entity_id']])
                ->andWhere(['state' => Products::STATE_APPROVED])
                ->andWhere(['status' => Products::STATUS_ENABLED])
                ->andWhere(['>', 'special_price', 0])
                ->andWhere(['<', 'special_from_date', $now])
                ->andWhere(['>', 'special_to_date', $now])
                ->column();
            $data[$merchantInfo['entity_id']]['special_product_number'] = count($product_ids);
        }
        return $data;
    }

    /**
     * @param $rules
     * @param $wholesaler_id
     * Author XiaoQiang
     * 获取优惠规则信息
     * @param string $store_name
     * @return array
     */
    public static function getWholesalerPromotionMessageInTag($rules, $wholesaler_id, $store_name = '')
    {
        self::$wholesalerCoupon = false;
        $promotion_messages = [];
        if (empty($wholesaler_id) || !is_array($rules) || count($rules) == 0) {
            return $promotion_messages;
        }
        /** @var \service\message\common\PromotionRule $rule */
        foreach ($rules as $rule) {
            if ($rule->getWholesalerId() == $wholesaler_id) {
                $promotion_message = [];
                if ($rule->getCouponType() == 1) {
                    //活动 //1.满额减;2.满额折;3.满额赠;4.满量减;5.满量折;6.满量赠
                    switch ($rule->getPromotionType()) {
                        // 减
                        case 1:
                        case 4:
                            $icon = Tags::$ICON_JIAN;
                            $icon_text = Tags::$ICON_JIAN_TEXT;
                            break;
                        // 折
                        case 2:
                        case 5:
                            $icon = Tags::$ICON_ZHE;
                            $icon_text = Tags::$ICON_ZHE_TEXT;
                            break;
                        // 赠
                        case 3:
                        case 6:
                            $icon = Tags::$ICON_ZENG;
                            $icon_text = Tags::$ICON_ZENG_TEXT;
                            break;
                        // 默认"促"
                        default:
                            $icon = Tags::$ICON_CU;
                            $icon_text = Tags::$ICON_CU_TEXT;
                            break;
                    }

                    $promotion_message = [
                        'text' => $rule->getWholesalerDescription(),
                        'icon' => $icon,
                        'icon_text' => $icon_text,
                        //多品级活动 tag_url生成规则是固定的，不再使用tag_url字段
                        'url' => $rule->getType() == 2 ? self::RULE_TAG_URL_PREFIX . $rule->getRuleId() : $rule->getTagUrl(),
                    ];
                }

                if ($rule->getType() == 3) {
                    $rule_detail_list = [
                        ['title' => '活动名称', 'content' => $rule->getName()],
                    ];

                    $promotion_range = $store_name . "全部商品";
                    if ($rule->getSubsidiesLelaiIncluded() != 1) {
                        $promotion_range .= "<br/>特价商品不参与该活动";
                    }
                    $rule_detail_list [] = ['title' => '活动范围', 'content' => $promotion_range];

                    $rule_detail_list [] = ['title' => '活动时间', 'content' => $rule->getFromDate() . "至<br/>" . $rule->getToDate()];
                    $rule_uses_limit = $rule->getRuleUsesLimit();
                    $uses_limit_text = $rule_uses_limit ? '每人限制' . $rule_uses_limit . '次' : '不限制次数';
                    $rule_detail_list [] = ['title' => '参与次数', 'content' => $uses_limit_text];
                    $rule_detail_list [] = ['title' => '活动详情', 'content' => $rule->getRuleDetail() ? $rule->getRuleDetail() : ''];

                    $promotion_message['promotion_detail'] = $rule_detail_list;

                    //订单级优惠券
                    if ($rule->getCouponType() != 1){
                        self::$wholesalerCoupon = true;
                    }
                }

                if (!empty($promotion_message)) {
                    array_push($promotion_messages, $promotion_message);
                }
            }
        }

        return $promotion_messages;
    }

    /**
     * 用于统计综合得分脚本获取供应商全部优惠信息
     * @param $wholesaler_ids
     * @return array|\service\message\common\PromotionRule[]
     */
    public static function getWholesalerAllPromotions($wholesaler_ids)
    {
        $rules = MerchantProxy::getAllSaleRule($wholesaler_ids);
        $promotions = [];
        if ($rules) {
            $promotions = $rules->getPromotions();
        }
        return $promotions;
    }

}