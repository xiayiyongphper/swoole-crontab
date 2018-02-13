<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-5
 * Time: 上午10:38
 */

namespace service\tasks\contractor;

use service\tasks\TaskService;

/**
 * Class calculateVisitScore
 * @package service\tasks\contractor
 * 0 1 * * *
 */
class calculateVisitScore extends TaskService
{

    public function run($data)
    {
        $truncate = <<<SQL
truncate table lelai_user_visit_score_result;
SQL;

        $select = <<<SQL

INSERT INTO lelai_user_visit_score_result
  SELECT
    curdate() AS imp_date,
    type,
    city,
    area_id,
    contractor,
    entity_id,
    store_name,
    storekeeper,
    phone,
    register_date,
    gmv,
    imp_date_diff,
    order_lastdate,
    order_avg,
    gmv_agv_day,
    order_30,
    last_visited_at,
    order_date,
    CASE
    WHEN fen_xiadan <= 0
      THEN 1
    ELSE fen_xiadan
    END                                                              AS fen_xiadan,
    fen_gmv,
    fen_kedan,
    fen_order_30,
    fen_visit,
    CASE
    WHEN fen_xiadan IS NULL
      THEN 0
    ELSE fen_xiadan
    END                                                              AS lingshi_fen,
    CASE
    WHEN c.score IS NULL
      THEN 0
    ELSE c.score
    END                                                              AS fen_yunying,
    CASE
    WHEN fen_xiadan <= 0
      THEN 1
    ELSE fen_xiadan
    END + fen_gmv + fen_kedan + fen_order_30 + fen_visit + CASE
                                                           WHEN c.score IS NULL
                                                             THEN 0
                                                           ELSE c.score
                                                           END + CASE
                                                                 WHEN fen_xiadan IS NULL
                                                                   THEN 0
                                                                 ELSE fen_xiadan
                                                                 END AS fen_all
  FROM (
         SELECT
           'old_user'                          AS type,
           city,
           area_id,
           contractor,
           entity_id,
           store_name,
           storekeeper,
           phone,
           register_date,
           gmv,
           imp_date_diff,
           datediff(curdate(), order_lastdate) AS order_lastdate,
           order_avg,
           gmv_agv_day,
           order_30,
           last_visited_at,
           order_date,
           CASE
           WHEN order_30 = order_date
             THEN 50 * CASE
                       WHEN imp_date_diff >= 8
                         THEN 8
                       ELSE imp_date_diff
                       END / 8
           WHEN register_date <= 60
                AND order_30 >= order_date - 2
             THEN 40 * CASE
                       WHEN imp_date_diff >= 8
                         THEN 8
                       ELSE imp_date_diff
                       END / 8
           WHEN order_date >= 10
                AND datediff(curdate(), order_lastdate) >= 0
                AND datediff(curdate(), order_lastdate) <= 7
             THEN (datediff(curdate(), order_lastdate) - order_avg) * 2 + 15
           WHEN order_date >= 10
                AND datediff(curdate(), order_lastdate) >= 8
                AND datediff(curdate(), order_lastdate) <= 15
             THEN (datediff(curdate(), order_lastdate) - order_avg) * 2 + 20
           WHEN order_date >= 10
                AND datediff(curdate(), order_lastdate) >= 16
                AND datediff(curdate(), order_lastdate) <= 30
             THEN CASE
                  WHEN datediff(curdate(), order_lastdate) - order_avg >= 10
                    THEN 10
                  WHEN order_avg > 15
                    THEN 8
                  ELSE datediff(curdate(), order_lastdate) - order_avg
                  END * 2 + 30
           WHEN order_date >= 10
                AND datediff(curdate(), order_lastdate) > 30
             THEN round(order_date / 10) * 2 + CASE
                                               WHEN datediff(curdate(), order_lastdate) <= 60
                                                 THEN 10
                                               ELSE 0
                                               END - CASE
                                                     WHEN datediff(curdate(), last_visited_at) <= 30
                                                       THEN 10
                                                     ELSE 0
                                                     END
           WHEN order_date < 10
                AND imp_date_diff >= 1
                AND imp_date_diff <= 30
             THEN 40 * (CASE
                        WHEN imp_date_diff > 15
                          THEN 15
                        ELSE imp_date_diff
                        END / 15)
           WHEN order_date < 10
                AND datediff(curdate(), order_lastdate) > 30
             THEN 20
           ELSE 0
           END                                 AS fen_xiadan,
           CASE
           WHEN order_30 = order_date
             THEN 20 * CASE
                       WHEN imp_date_diff >= 8
                         THEN 8
                       ELSE imp_date_diff
                       END / 8
           WHEN register_date <= 60
                AND order_30 >= order_date - 2
             THEN 20 * CASE
                       WHEN imp_date_diff >= 8
                         THEN 8
                       ELSE imp_date_diff
                       END / 8
           WHEN round(gmv / 5000) > 10
             THEN 20
           WHEN round(gmv / 5000) = 0
             THEN 1
           ELSE round(gmv / 5000) * 2
           END                                 AS fen_gmv,
           CASE
           WHEN round(gmv_agv_day / 100) > 10
             THEN 10
           WHEN round(gmv_agv_day / 100) = 0
             THEN 1
           ELSE round(gmv_agv_day / 100)
           END                                 AS fen_kedan,
           CASE
           WHEN order_30 > 5
             THEN 10
           WHEN order_30 = 0
             THEN 1
           ELSE order_30 * 2
           END                                 AS fen_order_30,
           CASE
           WHEN datediff(curdate(), last_visited_at) < 10
             THEN 0
           WHEN datediff(curdate(), last_visited_at) > 30
             THEN 10
           ELSE
             CASE
             WHEN (30 - datediff(curdate(), last_visited_at)) / 2 IS NULL
               THEN 0
             ELSE (30 - datediff(curdate(), last_visited_at)) / 2
             END
           END                                 AS fen_visit
         FROM lelai_user_portrait_result
         WHERE gmv > 0
               AND register_date > 30
         UNION ALL
         SELECT
           'new_user'                          AS type,
           city,
           area_id,
           contractor,
           entity_id,
           store_name,
           storekeeper,
           phone,
           register_date,
           gmv,
           imp_date_diff,
           datediff(curdate(), order_lastdate) AS order_lastdate,
           order_avg,
           gmv_agv_day,
           order_30,
           last_visited_at,
           order_date,
           CASE
           WHEN order_date >= 1
                AND (datediff(curdate(), last_visited_at) > 10
                     OR datediff(curdate(), last_visited_at) IS NULL)
             THEN CASE
                  WHEN datediff(curdate(), order_lastdate) > 10
                    THEN 10
                  ELSE datediff(curdate(), order_lastdate)
                  END / 10 * 90
           WHEN (order_date IS NULL
                 OR order_date = '')
                AND (datediff(curdate(), last_visited_at) > 10
                     OR datediff(curdate(), last_visited_at) IS NULL)
             THEN CASE
                  WHEN register_date > 10
                    THEN 10
                  ELSE register_date
                  END / 10 * 90
           ELSE 1
           END                                 AS fen_xiadan,
           0                                   AS fen_gmv,
           0                                   AS fen_kedan,
           0                                   AS fen_order_30,
           0                                   AS fen_visit
         FROM lelai_user_portrait_result
         WHERE register_date <= 30
       ) a
    LEFT JOIN (
                SELECT
                  customer_id,
                  CASE
                  WHEN order_all > 10
                       AND order_lelai >= 5
                    THEN 30
                  WHEN order_all > 10
                       AND order_lelai >= 3
                    THEN 25
                  WHEN order_all > 10
                       AND order_lelai >= 1
                    THEN 20
                  WHEN order_all > 10
                    THEN 15
                  WHEN order_all > 3
                    THEN 10
                  WHEN order_all >= 1
                    THEN 5
                  ELSE 0
                  END AS lingshi_fen
                FROM (
                       SELECT
                         customer_id,
                         COUNT(DISTINCT CASE
                                        WHEN (first_category_id in (484,485,486,487,488,489,492,493,494) or (second_category_id in (513,514)) or first_category_id in (31,103,127,413,2,161,269,213)) 
                                          THEN entity_id
                                        ELSE NULL
                                        END) AS order_all,
                         COUNT(DISTINCT CASE
                                        WHEN wholesaler_id IN (
                                          SELECT le_merchant_store.entity_id
                                          FROM lelai_slim_merchant.le_merchant_store
                                          WHERE store_type = 6
                                        )
                                          THEN entity_id
                                        ELSE NULL
                                        END) AS order_lelai
                       FROM (
                              SELECT DISTINCT
                                entity_id,
                                customer_id,
                                imp_date,
                                wholesaler_id,
                                first_category_id,
                                second_category_id
                              FROM (
                                     SELECT
                                       entity_id,
                                       customer_id,
                                       wholesaler_id,
                                       substring(addtime(created_at, '8:00:00'), 1, 10) AS imp_date
                                     FROM lelai_slim_core.sales_flat_order
                                     WHERE substring(addtime(created_at, '8:00:00'), 1, 10) >= subdate(curdate(), 91)
                                           AND wholesaler_name NOT LIKE '%t%'
                                           AND wholesaler_name NOT LIKE '%T%'
                                           AND wholesaler_name NOT LIKE '%特通渠道%'
                                           AND wholesaler_name NOT LIKE '%乐来供应链%'
                                           AND wholesaler_name NOT LIKE '%测试%'
                                           AND customer_tag_id = 1
                                           AND contractor_id NOT IN (1, 22, 26, 39, 112, 114, 116, 171, 185, 202)
                                           AND wholesaler_id NOT IN (2, 4, 5, 12, 42, 260)
                                           AND customer_id NOT IN
                                               (1021, 1206, 1208, 1215, 1245, 2299, 2376, 2476, 1942, 1650, 2541)
                                           AND STATUS IN
                                               ('processing_receive', 'processing_shipping', 'pending_comment', 'processing', 'complete')
                                   ) a
                                LEFT JOIN (
                                            SELECT DISTINCT
                                              order_id,
                                              first_category_id,
                                              second_category_id
                                            FROM lelai_slim_core.sales_flat_order_item
                                            WHERE
                                              substring(addtime(created_at, '8:00:00'), 1, 10) >= subdate(curdate(), 91)
                                          ) b
                                  ON a.entity_id = b.order_id
                            ) a
                       GROUP BY customer_id
                     ) a
                WHERE customer_id IS NOT NULL
                GROUP BY customer_id
              ) b
      ON a.entity_id = b.customer_id
    LEFT JOIN (
                SELECT
                  a.customer_id,
                  score
                FROM (
                       SELECT
                         customer_id,
                         substring(end_time, 1, 10)   AS end_date,
                         substring(start_time, 1, 10) AS start_date,
                         sum(score) as score
                       FROM lelai_slim_customer.contractor_visit_task
                       WHERE substring(end_time, 1, 10) >= curdate()
                             AND substring(start_time, 1, 10) <= curdate()
                             AND status = 1
                             group by customer_id,
                          substring(end_time, 1, 10),
                          substring(start_time, 1, 10)
                     ) a
                  LEFT JOIN (
                              SELECT
                                customer_id,
                                MAX(substring(created_at, 1, 10)) AS imp_date
                              FROM lelai_slim_customer.contractor_visit_records
                              GROUP BY customer_id
                            ) b
                    ON a.customer_id = b.customer_id
                WHERE imp_date < start_date
              ) c
      ON a.entity_id = c.customer_id
  WHERE datediff(curdate(), last_visited_at) > 7;
SQL;
        /** @var \yii\db\Connection $resultDb */
        $resultDb = \Yii::$app->resultDb;
        $resultDb->createCommand($truncate)->query();
        $queryData = $resultDb->createCommand($select)->query();
        $this->log($queryData);
    }
}