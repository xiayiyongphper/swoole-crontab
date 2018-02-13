<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-5
 * Time: 上午10:38
 */

namespace service\tasks\contractor;

use common\models\contractor\ContractorMetrics;
use common\models\contractor\ContractorTaskHistory;
use framework\components\ToolsAbstract;
use service\tasks\TaskService;

class snacksGmv extends TaskService
{
    const METRIC_IDENTIFIER = 'snacks_gmv';

    public function run($data)
    {
        $this->log('freshOrderGmv-start');
        $metricId = ContractorMetrics::getMetricIdByIdentifier(self::METRIC_IDENTIFIER);
        if ($metricId === false) {
            $this->log(sprintf('Metric:%s not found', self::METRIC_IDENTIFIER));
            return false;
        }
        $coreDb = $this->getConnection();
        $sql = $this->getRawSql($metricId);
        $this->log($sql);
        $command = $coreDb->createCommand($this->getRawSql($metricId));
        $reader = $command->query();
        if ($reader->getRowCount() === 0) {
            $this->log('没有找到相关记录');
            return false;
        }
        $contractorDb = ContractorMetrics::getDb();
        $transaction = $contractorDb->beginTransaction();
        $date = ToolsAbstract::getDate();
        try {
            foreach ($reader as $row) {
                if (!isset($row['city'], $row['owner_id'], $row['date'], $row['value'])) {
                    //当业务员当前没有分配商品时，跳过
                    $this->log('指标数据格式不正确，跳过');
                    $this->log($row);
                    continue;
                }
                $city = $row['city'];
                $contractorId = $row['owner_id'];
                $day = $row['date'];
                $value = $row['value'];
                $updatedAt = $date->date();
                $history = ContractorTaskHistory::findOne(['city' => $city, 'owner_id' => $contractorId, 'metric_id' => $metricId, 'date' => $day]);
                if (!$history) {
                    $sql = 'INSERT' . ' INTO ' . ContractorTaskHistory::tableName() . " (`value`,`date`,`city`,`owner_id`,`metric_id`,`updated_at`) VALUES($value,'$day',$city,$contractorId,$metricId,'$updatedAt')";
                } else {
                    $sql = 'UPDATE ' . ContractorTaskHistory::tableName() . ' SET value = ' . $value . ', updated_at = "' . $updatedAt . '" WHERE `city` = ' . $city . ' AND owner_id = ' . $contractorId . ' AND metric_id = ' . $metricId . ' AND date = ' . '"' . $day . '"';
                }
                $this->log($sql);
                $contractorDb->createCommand($sql)->execute();
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->log($e->__toString());
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->log($e->__toString());
        }
        $this->log('freshOrderGmv-finished');
        return true;
    }

    /**
     * @return \yii\db\Connection
     */
    protected function getConnection()
    {
        return \Yii::$app->get('coreReadOnlyDb');
    }

    /**
     * @param $metricId
     * @param int $storeType 零食项目的店铺类型
     * @return string
     */
    private function getRawSql($metricId, $storeType = 6)
    {
        return <<<SQL
select
sum(case when STATUS IN ('processing_receive', 'processing_shipping', 'pending_comment', 'processing', 'complete') then subtotal else 0 end) as value,
substring(addtime(created_at,'8:00:00'),1,10) as date,city,contractor_id as owner_id,
$metricId as metric_id,concat( curdate(),' ',curtime()) as updated_at
  FROM
  (
select
  entity_id,created_at,status,city,contractor_id
from lelai_slim_core.sales_flat_order
where substring(addtime(created_at,'8:00:00'),1,10)>=subdate(curdate(),9) and substring(addtime(created_at,'8:00:00'),1,10)<= curdate()
and wholesaler_id in (SELECT le_merchant_store.entity_id from lelai_slim_merchant.le_merchant_store where store_type=$storeType)
and wholesaler_name NOT LIKE '%t%'
AND wholesaler_name NOT LIKE '%T%'
AND wholesaler_name NOT LIKE '%特通渠道%'
and wholesaler_name not like '%乐来供应链%'
and wholesaler_name not like '%测试%'
and customer_tag_id=1
and contractor_id not in (1,22,26,39,112,114,116,171,185,202) and wholesaler_id not in (2,4,5,12,42,260) and customer_id not in (1021,1206,1208,1215,1245,2299,2376,1942,1650,2541)
) a
    left join
    (
      SELECT order_id,row_total as subtotal from lelai_slim_core.sales_flat_order_item where substring(addtime(created_at,'8:00:00'),1,10)>=subdate(curdate(),9) and substring(addtime(created_at,'8:00:00'),1,10)<= curdate()
      and  ( first_category_id in (484,485,486,487,488,489,492,493,494) or (second_category_id in (513,514)) or first_category_id in (31,103,127,413,2,161,269,213) ) 
      ) b on a.entity_id=b.order_id
group by substring(addtime(created_at,'8:00:00'),1,10) ,city,contractor_id
union  all
select
sum(case when STATUS IN ('processing_receive', 'processing_shipping', 'pending_comment', 'processing', 'complete') then subtotal else 0 end) as value,
  substring(addtime(created_at,'8:00:00'),1,10) as date,city,0 as owner_id,$metricId as metric_id,concat( curdate(),' ',curtime()) as updated_at
from

  (
    select
  entity_id,created_at,status,city,contractor_id
from  lelai_slim_core.sales_flat_order
    WHERE substring(addtime(created_at, '8:00:00'), 1, 10)>=subdate(curdate(), 9) AND substring(addtime(created_at, '8:00:00'), 1, 10)<= curdate()
    AND wholesaler_id IN ( SELECT le_merchant_store.entity_id FROM lelai_slim_merchant.le_merchant_store WHERE store_type=$storeType)
    AND wholesaler_name NOT LIKE '%t%'
    AND wholesaler_name NOT LIKE '%T%'
    AND wholesaler_name NOT LIKE '%特通渠道%'
    AND wholesaler_name NOT LIKE '%乐来供应链%'
    AND wholesaler_name NOT LIKE '%测试%'
    AND customer_tag_id=1
    AND contractor_id NOT IN (1, 22, 26, 39, 112, 114, 116, 171, 185, 202) AND wholesaler_id NOT IN (2, 4, 5, 12, 42, 260) AND customer_id NOT IN (1021, 1206, 1208, 1215, 1245, 2299, 2376, 1942, 1650, 2541)
  ) a 
    left join
    (
      SELECT order_id,row_total as subtotal from lelai_slim_core.sales_flat_order_item where substring(addtime(created_at,'8:00:00'),1,10)>=subdate(curdate(),9) and substring(addtime(created_at,'8:00:00'),1,10)<= curdate()
      and  ( first_category_id in (484,485,486,487,488,489,492,493,494) or (second_category_id in (513,514)) or first_category_id in (31,103,127,413,2,161,269,213) ) 
      ) b on a.entity_id=b.order_id
group by substring(addtime(created_at,'8:00:00'),1,10) ,city;
SQL;

    }

}