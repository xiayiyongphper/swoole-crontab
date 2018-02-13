<?php
/**
 * Created by PhpStorm.
 * User: Ryan Hong
 * Date: 2017/11/28
 * Time: 14:41
 */

namespace service\tasks\contractor;

use common\helpers\Tools;
use common\models\customer\CustomerShelvesProduct;
use common\models\result\GoodLsinCycResult;
use common\models\LeCustomers;
use framework\components\Date;
use service\tasks\TaskService;

/**
 * Class updateShelvesProduct
 * @package service\tasks\contractor
 */
class updateShelvesProduct extends TaskService
{
    public function run($data)
    {
        $round = 0;
        $max_id = 0;
        $row_num = 100;
        $date = new Date();
        $today = $date->date("Y-m-d");
        while(true){
            echo "round $round start".PHP_EOL;

            $rows = CustomerShelvesProduct::find()
                //->alias('shelves')
                //->select(['shelves.*','customer.city'])
                //->leftJoin(['customer' => 'lelai_slim_customer.le_customers'],'customer.entity_id = customer_id')
                ->where(['>','entity_id',$max_id])
                ->orderBy('entity_id ASC')
                ->limit($row_num);
            $rows = $rows->all();

            if(empty($rows)) {
                echo "task finished".PHP_EOL;
                break;
            }

           // Tools::log($rows[0],'shelve.log');
            foreach ($rows as $row) {
                $max_id = $row['entity_id'];

                $customer = LeCustomers::findByCustomerId($row->customer_id);
                if(empty($customer)) continue;

                //获取平均进货周期
                $average_cycle = 0;
                $buy_cycle_info = GoodLsinCycResult::find()
                    ->where([
                        'city' => $customer->city,
                        'lsin' => $row->lsin
                    ])->asArray()->one();
                //Tools::log($buy_cycle_info,'shelve.log');
                if(!empty($buy_cycle_info)){
                    $average_cycle = $buy_cycle_info['city_cyc_date'];
                }

                //Tools::log($today.'===='.substr($row->latest_buy_time,0,10).'===='.$average_cycle,'shelve.log');
                //Tools::log((strtotime($today) - strtotime(substr($row->latest_buy_time,0,10))) / 24 /3600 ,'shelve.log');
                if($row->latest_buy_time == '0000-00-00 00:00:00'){//没买过，应该是手动加入货架
                    $row->buy_cycle_proportion = 100000;//要排最前面，所以设成尽可能大
                    $row->out_of_stock = 0;
                } elseif(empty($average_cycle)) {
                    $row->buy_cycle_proportion = 0;
                    $row->out_of_stock = 0;
                }else{
                    $days = (strtotime($today) - strtotime(substr($row->latest_buy_time,0,10))) / (24*3600);
                    $row->buy_cycle_proportion = $days / $average_cycle;
                    if($days >= ($average_cycle - 1)){
                        $row->out_of_stock = 1;
                    }else{
                        $row->out_of_stock = 0;
                    }
                }

                $row->save();
            }

            echo "round $round over".PHP_EOL;
            $round++;
        }

        return true;
    }

}