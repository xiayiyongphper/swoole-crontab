<?php
/**
 * 定时更新超市业务员名字
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 11:22
 */

namespace service\tasks\merchant;

use framework\components\ToolsAbstract;
use service\tasks\TaskService;

class updateCustomerContractor extends TaskService
{

    public function run($data)
    {
        ToolsAbstract::log('updateCustomerContractor','updateCustomerContractor.log');
        /** @var \yii\db\Connection $customerDb */
        $customerDb = \Yii::$app->get('customerDb');
        $sql = 'UPDATE `le_customers` as c set contractor = (SELECT name FROM contractor WHERE entity_id = c.contractor_id)';
        $command  = $customerDb->createCommand($sql);
        $result = $command->execute();
        ToolsAbstract::log($result,'updateCustomerContractor.log');
    }
}