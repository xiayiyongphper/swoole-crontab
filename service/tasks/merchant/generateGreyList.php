<?php
namespace service\tasks\merchant;

use common\helpers\MerchantProxy;
use common\models\merchant\GreyList;
use framework\components\ToolsAbstract;
use service\tasks\TaskService;

/**
 * 生成黑名单
 * @package service\tasks
 */
class generateGreyList extends TaskService
{
    //黑名单缓存，键前缀
    const GREY_LIST_KEY_PREFIX = 'sk_greylist_%s';

    /**
     * @inheritdoc
     */
    public function run($data)
    {
        //清空之前的灰名单
        $redis = ToolsAbstract::getRedis();
        $old_keys = $redis->keys('sk_greylist_*');
        //ToolsAbstract::log('$old_keys=====','greylist.log');
        //ToolsAbstract::log($old_keys,'greylist.log');
        foreach ($old_keys as $key) {
            $redis->del($key);
        }

        //获取所有灰名单规则
        $grey_rules = GreyList::find()->asArray()->all();

        ToolsAbstract::log('$grey_rules=========', 'greylist.log');
        ToolsAbstract::log($grey_rules, 'greylist.log');

        //获取灰名单
        $grey_list = array();
        if (!empty($grey_rules)) {
            /** @throws \Exception */
            $grey_list = MerchantProxy::getGreyList($grey_rules);
        }

        ToolsAbstract::log('$grey_list=========', 'greylist.log');
        ToolsAbstract::log($grey_list, 'greylist.log');

        //写缓存
        foreach ($grey_list as $item) {
            $key = sprintf(self::GREY_LIST_KEY_PREFIX, $item['city']);
            $redis->hSet($key, intval($item['customer_id']), 1);
        }
    }
}