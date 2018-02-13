<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 17-11-6
 * Time: 上午10:34
 */

namespace common\helpers;


use common\components\Events;
use common\models\merchant\DeviceToken;
use common\models\LeMerchantStore;
use framework\components\ProxyAbstract;
use framework\components\ToolsAbstract;

class Tools extends ToolsAbstract
{
    /**
     * 拉取所有供货商id集合
     * @param int $categoryId
     * @param int $areaId
     * @return $this|array
     */
    public static function getAllWholesalerIds($categoryId = 0, $areaId = 0)
    {
        $merchantModel = new LeMerchantStore();
        $wholesalerIds = $merchantModel::find()->where(['status' => LeMerchantStore::STATUS_NORMAL]);
        if ($areaId > 0) {
            $wholesalerIds->andWhere(['like', 'area_id', '|' . $areaId . '|']);
        }
        if ($categoryId > 0) {
            $wholesalerIds->andWhere(['like', 'store_category', '|' . $categoryId . '|']);
        }
        $wholesalerIds->andWhere(['>=', 'sort', 0])->orderBy('sort_score desc, sort desc');
        $wholesalerIds = $wholesalerIds->column();
        //Tools::log('-----------$wholesalerIds:' . print_r($wholesalerIds, true), 'debug.txt');
        return $wholesalerIds;
    }

    public static function notifyOrder($wholesalerId, $orderId, $content, $sound = null)
    {
        // 拿到推送数据
        /** @var DeviceToken $device */
        $device = DeviceToken::find()->where(['merchant_id' => $wholesalerId])->orderBy('entity_id desc')->one();
        if(!$device){
            return false;
        }
        $scheme = 'lelaiwholesaler://order/info?oid=' . $orderId;
        if ($sound) {
            $scheme .= '&sound=' . $sound;
        }
        ProxyAbstract::sendMessage(Events::getCustomerEventName(Events::EVENT_PUSH_NOTIFICATION), array(
            'name' => Events::EVENT_PUSH_NOTIFICATION,
            'data' => array(
                'platform' => 2,// 1:订货网  2:商家版  我这边固定传2
                'system' => $device->system,
                'token' => $device->token,
                //'type'      => 2,  // 固定LE_Push_Model_Queue::TYPE_MERCHANT
                'value_id' => $wholesalerId,
                'channel' => $device->channel,
                'typequeue' => $device->typequeue,
                'params' => serialize(array(
                    'title' => '乐来订货',
                    'content' => $content,
                    'scheme' => $scheme,
                )),
            )
        ));
    }

    public static function random($low = 0, $high = 1, $decimals = 5)
    {
        $decimals = abs($decimals);
        if ($high < $low) {
            $t = $high;
            $high = $low;
            $low = $t;
        }
        $length = ($high - $low) * pow(10, $decimals);
        $dt = rand(0, $length);
        return $low + floatval($dt / pow(10, $decimals));
    }


    /**
     * 以下是cms用户分群的 函数方法
     */
    public static function getFirstErrorString($errors)
    {
        if (empty($errors)) {
            return '';
        } else {
            $errorString = '';
            foreach ($errors as $name => $es) {
                if (!empty($es)) {
                    $errorString = reset($es);
                    break;
                }
            }
            return $errorString;
        }
    }

    public static function getResources()
    {
        $file = \Yii::$app->getBasePath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'resources.json';
        $content = file_get_contents($file);
        return json_decode($content, true);
    }

    public static function getResourcesByContractor()
    {
        $file = \Yii::$app->getBasePath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'contractor.json';
        $content = file_get_contents($file);
        return json_decode($content, true);
    }

    /**
     * @param $tree
     * @param string $prefix
     */
    public static function transformTree(&$tree, $prefix = '')
    {
        foreach ($tree as $key => &$node) {
            $node['id'] = $prefix . $node['id'];
            if (isset($node['children']) && count($node['children']) > 0) {
                self::transformTree($node['children'], $node['id'] . '/');
            }
        }
    }

    /**
     * 导出EXcel格式数据
     * @param array $list 列表数据，二维数组，可空
     * @param array $first_row 首行标题，非空,形如 ['记录编号', '权重', 'LSIN号', '商品名称', '条形码']
     */
    public static function exportToExcel($list, $first_row)
    {
        // 循环写入结果
        $data_str = '';
        if (!empty($list)) {
            foreach ($list as $row) {
                //转row为一维数组
                $row = implode(',', $row);
//                $data_str .= mb_convert_encoding($row, 'GBK', 'UTF-8') . PHP_EOL;
                $data_str .= $row . PHP_EOL;
            }
            unset($list, $row);
        }

        //export data
        header("Content-Type: application/vnd.ms-excel; charset=GBK");
        header("Content-Transfer-Encoding: binary ");

        // 表头翻译
        if (!empty($first_row) && is_array($first_row)) {
//            echo iconv('UTF-8', 'GBK', implode(',', $first_row)) . PHP_EOL;
            echo implode(',', $first_row) . PHP_EOL;
        }
        echo $data_str;
    }

    /**
     * 根据角色id查角色名称
     * @param array $role_id 列表数据，二维数组，可空
     * @param array $first_row 首行标题，非空,形如 ['记录编号', '权重', 'LSIN号', '商品名称', '条形码']
     */
    /**
     * 用户分群方法结束
     */
}