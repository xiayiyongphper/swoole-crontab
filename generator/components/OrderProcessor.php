<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-26
 * Time: 上午10:30
 */

namespace generator\components;


use framework\components\ToolsAbstract;
use generator\models\SalesFlatOrder;
use generator\models\SalesFlatOrderStatusHistory;

class OrderProcessor
{
    protected $progressBarPrefix = '';
    protected $progressBarMedium = '';
    protected $progressBarSuffix = '';
    /**
     * 1.8kw
     * @var int
     */
    protected $min = 21650000;
    /**
     * 2.2kw
     * @var int
     */
    protected $max = 22000000;
    /**
     *
     * @var bool
     */
    protected $needCancelOrder = false;

    protected function getOrders()
    {
        $orderIds = [
            773298,
            773299,
            773300,
            773301,
            773302,
            773303,
            773304,
            773305,
            773306,
            773307,
            773308,
            773309,
            773310,
            773311,
            773312,
            773313,
            773314,
            773315,
            773316,
            773317,
            773318,
            773319,
            773320,
            773321,
            773322,
            773323,
            773324,
            773325,
            773326,
            773327,
            773328,
            773329,
            773330,
            773331,
            773332,
            773333,
            773334,
            773335,
            773336,
            773337,
            773338,
            773339,
            773340,
            773341,
            773342,
            773343,
            773344,
            773345,
            773346,
            773347,
            773348,
            773349,
            773350,
            773351,
            773352,
            773353,
            773354,
            773355,
            773356,
            773357,
            773358,
            773359,
            773360,
            773361,
            773362,
            773363,
            773364,
            773365,
            773366,
            773367,
            773368,
            773369,
            773370,
            773371,
            773372,
            773373,
            773374,
            773375,
            773376,
            773377,
            773378,
            773379,
            773380,
            773381,
            773382,
            773383,
            773384,
            773385,
            773386,
            773387,
            773388,
            773389,
            773390,
            773391,
            773392,
            773393,
            773394,
            773395,
            773396,
            773397,
            773398,
            773399,
            773400,
            773401,
            773402,
            773403,
            773404,
            773405,
            773406,
            773407,
            773408,
            773409,
            773410,
            773411,
            773412,
            773413,
            773414,
            773415,
            773416,
            773417,
            773418,
            773419,
            773420,
            773421,
            773422,
            773423,
            773424,
            773425,
            773426,
            773427,
            773428,
            773429,
            773430,
            773431,
            773432,
            773433,
            773434,
            773435,
            773436,
            773437,
            773438,
            773439,
            773440,
            773441,
            773442,
            773443,
            773444,
            773445,
            773446,
            773447,
            773448,
            773449,
            773450,
            773451,
            773452,
            773453,
            773454,
            773455,
            773456,
            773457,
            773458,
            773459,
            773460,
            773461,
            773462,
            773463,
            773464,
            773465,
            773466,
            773467,
            773468,
            773469,
            773470,
            773471,
            773472,
            773473,
            773474,
            773475,
            773476,
            773477,
            773478,
            773479,
            773480,
            773481,
            773482,
            773483,
            773484,
            773485,
            773486,
            773487,
            773488,
            773489,
            773490,
            773491,
            773492,
            773493,
            773494,
            773495,
            773496,
            773497,
            773498,
            773499,
            773500,
            773501,
            773502,
            773503,
            773504,
            773505,
            773506,
            773507,
            773508,
            773509,
            773510,
            773511,
            773512,
            773513,
            773514,
            773515,
            773516,
            773517,
            773518,
            773519,
            773520,
            773521,
            773522,
            773523,
            773524,
            773525,
            773526,
            773527,
            773528,
            773529,
            773530,
            773531,
            773532,
            773533,
            773534,
            773535,
            773536,
            773537,
            773538,
            773539,
            773540,
            773541,
            773542,
            773543,
            773544,
            773545,
            773546,
            773547,
            773548,
            773549,
            773550,
            773551,
            773552,
            773553,
            773554,
            773555,
            773556,
            773557,
            773558,
            773559,
            773560,
            773561,
            773562,
            773563,
            773564,
            773565,
            773566,
            773567,
            773568,
            773569,
            773570,
            773571,
            773572,
            773573,
            773574,
            773575,
            773576,
            773577,
            773578,
            773579,
            773580,
            773581,
            773582,
            773583,
            773584,
            773585,
            773586,
            773587,
            773588,
            773589,
            773590,
            773592,
            773593,
            773594,
            773595,
            773596,
            773597,
            773598,
            773599,
            773600,
            773601,
            773602,
            773603,
            773604,
            773605,
            773606,
            773607,
            773608,
            773609,
            773610,
            773611,
            773612,
            773613,
            773614,
            773615,
            773616,
            773617,
            773618,
            773619,
            773620,
            773621,
            773622,
            773623,
            773624,
            773625,
            773626,
            773627,
            773628,
            773629,
            773630,
            773631,
            773632,
            773633,
            773634,
            773635,
            773636,
            773637,
            773638,
            773639,
            773640,
            773641,
            773642,
            773643,
            773644,
            773645,
            773646,
            773647,
            773648,
            773649,
            773650,
            773651,
            773652,
            773653,
            773654,
            773655,
            773656,
            773657,
            773658,
            773659,
            773660,
            773661,
            773662,
            773663,
            773664,
            773665,
            773666,
            773667,
            773668,
            773669,
            773670,
            773671,
            773672,
            773673,
            773674,
            773675,
            773676,
            773677,
            773678,
            773679,
            773680,
            773681,
            773682,
            773683,
            773684,
            773685,
            773686,
            773687,
            773688,
            773689,
            773690,
            773691,
            773692,
            773693,
            773694,
            773695,
            773696,
            773697,
            773698,
            773699,
            773700,
            773701,
            773702,
            773703,
            773704,
            773705,
            773706,
            773707,
            773708,
            773709,
            773710,
            773711,
            773712,
            773713,
            773714,
            773715,
            773716,
            773717,
            773718,
            773719,
            773720,
            773721,
            773722,
            773723,
            773724,
            773725,
            773726,
            773727,
            773728,
            773729,
            773730,
            773731,
            773732,
            773733,
            773734,
            773735,
            773736,
            773737,
            773738,
            773739,
            773740,
            773741,
            773742,
            773743,
            773744,
            773745,
            773746,
            773747,
            773748,
            773749,
            773750,
            773751,
            773752,
            773753,
            773754,
            773755,
            773756,
            773757,
            773758,
            773759,
            773760,
            773761,
            773762,
            773763,
            773764,
            773765,
            773766,
            773767,
            773768,
            773769,
            773770,
            773771,
            773772,
            773773,
            773774,
            773775,
            773776,
            773777,
            773778,
            773779,
            773780,
            773781,
            773782,
            773783,
            773784,
            773785,
            773786,
            773787,
            773788,
            773789,
            773790,
            773791,
            773792,
            773793,
            773794,
            773795,
            773796,
            773797,
            773798,
            773799,
            773800,
            773801,
            773802,
            773803,
            773804,
            773805,
            773806,
            773807,
            773808,
            773809,
            773810,
            773811,
            773812,
            773814,
            773815,
            773816,
            773817,
            773818,
            773819,
            773820,
            773821,
            773822,
            773823,
            773824,
            773825,
            773826,
            773827,
            773828,
            773829,
            773830,
            773831,
            773832,
            773833,
            773834,
            773835,
            773836,
            773837,
            773838,
            773839,
            773840,
            773841,
            773842,
            773843,
            773844,
            773845,
            773846,
            773847,
            773848,
            773849,
            773850,
            773851,
            773852,
            773853,
            773854,
            773855,
            773856,
            773857,
            773858,
            773859,
            773860,
            773861,
            773862,
            773863,
            773864,
            773865,
            773866,
            773867,
            773868,
            773869,
            773870,
            773871,
            773872,
            773873,
            773874,
            773875,
            773876,
            773877,
            773878,
            773879,
            773880,
            773881,
            773882,
            773883,
            773884,
            773885,
            773886,
            773887,
            773888,
            773889,
            773890,
            773891,
            773892,
            773893,
            773894,
            773895,
            773896,
            773897,
            773898,
            773899,
            773900,
            773901,
            773902,
            773903,
            773904,
            773905,
            773906,
            773907,
            773908,
            773909,
            773910,
            773911,
            773912,
            773913,
            773914,
            773915,
            773916,
            773917,
            773918,
            773919,
            773920,
            773921,
            773922,
            773923,
            773924,
            773925,
            773926,
            773927,
            773928,
            773929,
            773930,
            773931,
            773932,
            773933,
            773934,
            773935,
            773936,
            773937,
            773938,
            773939,
            773940,
            773941,
            773942,
            773943,
            773944,
            773945,
            773946,
            773947,
            773948,
            773949,
            773950,
            773951,
            773952,
            773953,
            773954,
            773955,
            773956,
            773957,
            773958,
            773959,
            773960,
            773961,
            773962,
            773963,
            773964,
            773965,
            773966,
            773967,
            773968,
            773969,
            773970,
            773971,
            773972,
            773973,
            773974,
            773975,
            773976,
            773977,
            773978,
            773979,
            773980,
            773981,
            773982,
            773983,
            773984,
            773985,
            773986,
            773987,
            773988,
            773989,
            773990,
            773991,
            773992,
            773993,
            773994,
            773995,
            773996,
            773997,
            773998,
            773999,
            774000,
            774001,
            774002,
            774003,
            774004,
            774005,
            774006,
            774007,
            774008,
            774009,
            774010,
            774011,
            774012,
            774013,
            774014,
            774015,
            774016,
            774017,
            774018,
            774019,
            774020,
            774021,
            774022,
            774023,
            774024,
            774025,
            774026,
            774027,
            774028,
            774029,
            774030,
            774031,
            774032,
            774033,
            774034,
            774035,
            774036,
            774037,
            774038,
            774039,
            774040,
            774041,
            774042,
            774043,
            774044,
            774045,
            774046,
            774047,
            774048,
            774049,
            774050,
            774051,
            774052,
            774053,
            774054,
            774055,
            774056,
            774057,
            774058,
            774059,
            774060,
            774061,
            774062,
            774063,
            774064,
            774065,
            774066,
            774067,
            774068,
            774069,
            774070,
            774071,
            774072,
            774073,
            774074,
            774075,
            774076,
            774077,
            774078,
            774079,
            774080,
            774081,
            774082,
            774083,
            774084,
            774085,
            774086,
            774087,
            774088,
            774089,
            774090,
            774091,
            774092,
            774093,
            774094,
            774095,
            774096,
            774097,
            774098,
            774099,
            774100,
            774101,
            774102,
            774103,
            774104,
            774105,
            774106,
            774107,
            774108,
            774109,
            774110,
            774111,
            774112,
            774113,
            774114,
            774115,
            774116,
            774117,
            774118,
            774119,
            774120,
            774121,
            774122,
            774123,
            774124,
            774125,
            774126,
            774127,
            774128,
            774129,
            774130,
            774131,
            774132,
            774133,
            774134,
            774135,
            774136,
            774137,
            774138,
            774139,
            774140,
            774141,
            774142,
            774143,
            774144,
            774145,
            774146,
            774147,
            774148,
            774149,
            774150,
            774151,
            774152,
            774153,
            774154,
            774155,
            774156,
            774157,
            774158,
            774159,
            774160,
            774161,
            774162,
            774163,
            774164,
            774165,
            774166,
            774167,
            774168,
            774169,
            774170,
            774171,
            774172,
            774173,
            774174,
            774175,
            774176,
            774177,
            774178,
            774179,
            774180,
            774181,
            774182,
            774183,
            774184,
            774185,
            774186,
            774187,
            774188,
            774189,
            774190,
            774191,
            774192,
            774193,
            774194,
            774195,
            774196,
            774197,
            774198,
            774199,
            774200,
            774201,
            774202,
            774203,
            774204,
            774205,
            774206,
            774207,
            774208,
            774209,
            774210,
            774211,
            774212,
            774213,
            774214,
            774215,
            774216,
            774217,
            774218,
            774219,
            774220,
            774221,
            774222,
            774223,
            774224,
            774225,
            774226,
            774227,
            774228,
            774229,
            774230,
            774231,
            774232,
            774233,
            774234,
            774235,
            774236,
            774237,
            774238,
            774239,
            774240,
            774241,
            774242,
            774243,
            774244,
            774245,
            774246,
            774247,
            774248,
            773813,
        ];
        $orders = SalesFlatOrder::find()
            ->addSelect(['entity_id', 'grand_total'])
//            ->andWhere(['customer_tag_id' => 7])
//            ->andWhere(['merchant_type_id' => 7])
//            ->andWhere(['<', 'created_at', '2017-10-01 00:00:00'])
            ->where(['entity_id' => $orderIds])
            ->asArray()
            ->all();
        return $orders;
    }

    public function run()
    {
        $orders = $this->getOrders();
        $selectedOrders = [];
        if ($this->needCancelOrder) {
            while (true) {
                shuffle($orders);
                $order = array_pop($orders);
                $grandTotal = $order['grand_total'];
                $testTotal = array_sum($selectedOrders) + $grandTotal;

                if ($testTotal > $this->max) {
                    //跳过，不符合需求，将取出来的订单放回去
                    array_push($orders, $order);
                    continue;
                }
                $selectedOrders[$order['entity_id']] = $order['grand_total'];

                if ($testTotal >= $this->min AND $testTotal <= $this->max) {
                    break;
                }
            }
        }

        $this->log('$testTotal:' . array_sum($selectedOrders));
        $this->log('$testTotalCount:' . count($selectedOrders));
        $this->log($selectedOrders);
        $this->log('restOrderCount:' . count($orders));

        $selectedOrderCount = count($selectedOrders);
        $times = 100;
        $index = 0;
        foreach ($selectedOrders as $orderId => $grandTotal) {
            $index += 1;
            $this->log($orderId);
            $percent = floor($index * $times / $selectedOrderCount);
            $this->progressBarPrefix = sprintf("Canceled Order Processing. %3s/%-3s[%s>%s] %s%% \t", $index, $selectedOrderCount, str_repeat('=', $percent), str_repeat('-', $times - $percent), $percent);
            $this->clearOrderStatusHistory($orderId);
            $cancelOrder = $this->getOrder($orderId);
            $this->processCanceledOrder($cancelOrder);
            $this->flushProgressBar();
        }

        $this->flushProgressBar(true);
        $index = 0;
        $ordersCount = count($orders);
        foreach ($orders as $key => $value) {
            $index += 1;
            $this->log($value['entity_id']);
            $percent = floor($index * $times / $ordersCount);
            $this->progressBarPrefix = sprintf("Normal Order Processing. %3s/%-3s[%s>%s] %s%% \t", $index, $ordersCount, str_repeat('=', $percent), str_repeat('-', $times - $percent), $percent);
            $this->clearOrderStatusHistory($value['entity_id']);
            $normalOrder = $this->getOrder($value['entity_id']);
            $this->processNormalOrder($normalOrder);
            $this->flushProgressBar();
        }
        $this->flushProgressBar(true);
    }

    /**
     * @param int $orderId
     * @return SalesFlatOrder
     */
    protected function getOrder(int $orderId): SalesFlatOrder
    {
        /** @var SalesFlatOrder $order */
        $order = SalesFlatOrder::findOne(['entity_id' => $orderId]);
        return $order;
    }

    /**
     * 清除订单的历史状态记录
     * @param int $orderId
     */
    protected function clearOrderStatusHistory(int $orderId)
    {
        SalesFlatOrderStatusHistory::deleteAll(['parent_id' => $orderId]);
    }

    /**
     * 取消订单流量
     * @param SalesFlatOrder $order
     */
    protected function processCanceledOrder(SalesFlatOrder $order)
    {
        $createdAtTimestamp = strtotime($order->created_at);//建立订单时间戳
        $order->setState(SalesFlatOrder::STATE_NEW, SalesFlatOrder::STATUS_PENDING, '', $this->formatDate($createdAtTimestamp));
        $order->setState(SalesFlatOrder::STATE_PROCESSING, SalesFlatOrder::STATUS_PROCESSING, '', $this->formatDate($createdAtTimestamp));
        $maxTry = 5;
        //接单时间戳
        $canceledTimestamp = false;
        while ($maxTry--) {
            $randomHour = rand(24, 48);
            $canceledTimestamp = strtotime("+$randomHour hour", $createdAtTimestamp);
            $hour = intval(date('G', $canceledTimestamp));
            if ($hour >= 1 AND $hour <= 11) {
                break;
            }
        }
        $order->setState(SalesFlatOrder::STATE_CANCELED, SalesFlatOrder::STATUS_CANCELED, '', $this->formatDate($canceledTimestamp));
        $order->updated_at = $this->formatDate($canceledTimestamp);//订单更新时间
        $order->complete_at = null;//订单完成时间
        $order->receipt = 0;//确认收货状态
        $order->receipt_total = 0;//确认收货金额
        $order->save();
    }

    /**
     * @param $timestamp
     * @return false|string
     */
    protected function formatDate($timestamp)
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * 正常订单流程
     * @param SalesFlatOrder $order
     */
    protected function processNormalOrder(SalesFlatOrder $order)
    {
        $createdAtTimestamp = strtotime($order->created_at);//建立订单时间戳
        $order->setState(SalesFlatOrder::STATE_NEW, SalesFlatOrder::STATUS_PENDING, '', $this->formatDate($createdAtTimestamp));
        $order->setState(SalesFlatOrder::STATE_PROCESSING, SalesFlatOrder::STATUS_PROCESSING, '', $this->formatDate($createdAtTimestamp));
        $maxTry = 5;
        //接单时间戳
        $receiveTimestamp = false;
        while ($maxTry--) {
            $randomHour = rand(1, 12);
            $receiveTimestamp = strtotime("+$randomHour hour", $createdAtTimestamp);
            $hour = intval(date('G', $receiveTimestamp));
            if ($hour >= 1 AND $hour <= 11) {
                break;
            }
        }
        $order->setState(SalesFlatOrder::STATE_PROCESSING, SalesFlatOrder::STATUS_PROCESSING_RECEIVE, '', $this->formatDate($receiveTimestamp));

        //确认收货时间戳
        $receiptConfirmTimestamp = false;
        $maxTry = 5;
        while ($maxTry--) {
            $randomHour = rand(24, 48);
            $receiptConfirmTimestamp = strtotime("+$randomHour hour", $receiveTimestamp);
            $hour = intval(date('G', $receiptConfirmTimestamp));
            if ($hour >= 1 AND $hour <= 11) {
                break;
            }
        }
        $order->setState(SalesFlatOrder::STATE_COMPLETE, SalesFlatOrder::STATUS_PENDING_COMMENT, '', $this->formatDate($receiptConfirmTimestamp));
        $randomHour = rand(10080, 10200);
        $commentTimestamp = strtotime("+$randomHour minute", $createdAtTimestamp);
        $order->setState(SalesFlatOrder::STATE_COMPLETE, SalesFlatOrder::STATUS_COMPLETE, '系统自动评价', $this->formatDate($commentTimestamp));
        $order->updated_at = $this->formatDate($receiptConfirmTimestamp);//订单更新时间
        $order->complete_at = $this->formatDate($receiptConfirmTimestamp);//订单完成时间
        $order->receipt = 1;//确认收货状态
        $order->receipt_total = $order->grand_total;//确认收货金额
        //订单子商品的确认收货字段使用sql更新
        $order->save();
    }

    protected function log($msg, $filename = 'order-process.log')
    {
        ToolsAbstract::log($msg, $filename);
    }

    protected function echo($msg)
    {
        echo $msg . "\r";
    }

    /**
     * @return string
     */
    public function getProgressBarPrefix(): string
    {
        return $this->progressBarPrefix;
    }

    /**
     * @param string $progressBarPrefix
     */
    public function setProgressBarPrefix(string $progressBarPrefix)
    {
        $this->progressBarPrefix = $progressBarPrefix;
    }

    /**
     * @return string
     */
    public function getProgressBarMedium(): string
    {
        return $this->progressBarMedium;
    }

    /**
     * @param string $progressBarMedium
     */
    public function setProgressBarMedium(string $progressBarMedium)
    {
        $this->progressBarMedium = $progressBarMedium;
    }

    /**
     * @return string
     */
    public function getProgressBarSuffix(): string
    {
        return $this->progressBarSuffix;
    }

    /**
     * @param string $progressBarSuffix
     */
    public function setProgressBarSuffix(string $progressBarSuffix)
    {
        $this->progressBarSuffix = $progressBarSuffix;
    }


    protected function flushProgressBar($newLine = false)
    {
        if ($newLine) {
            echo $this->progressBarPrefix . $this->progressBarMedium . $this->progressBarSuffix . "\n";
        } else {
            echo $this->progressBarPrefix . $this->progressBarMedium . $this->progressBarSuffix . "\r";
        }
    }
}