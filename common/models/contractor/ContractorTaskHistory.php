<?php

namespace common\models\contractor;

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/7/18
 * Time: 17:10
 */


use framework\components\ToolsAbstract;
use framework\db\ActiveRecord;
use Yii;

/**
 * Class ContractorTaskHistory
 * @package common\models\contractor
 * @property integer $entity_id
 * @property string|float $value
 * @property string $date
 * @property int city
 * @property int owner_id
 * @property int metric_id
 * @property string $updated_at
 */
class ContractorTaskHistory extends ActiveRecord
{

    public $sales_total;
    public $orders_count;

    /**
     * 环比昨日
     * @var string
     */
    public $today_on_yesterday;
    /**
     * 同比上周
     * @var string
     */
    public $today_on_lastweek;

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('customerDb');
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contractor_task_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['value'], 'number', 'min' => 0, 'max' => 99999999.99],
            [['city', 'metric_id'], 'number', 'min' => 1, 'max' => PHP_INT_MAX],
            [['owner_id'], 'number', 'min' => 0, 'max' => PHP_INT_MAX],
            [['owner_id', 'metric_id', 'city', 'value'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->updated_at = ToolsAbstract::getDate()->date('Y-m-d H:i:s');
        return parent::beforeSave($insert);
    }
}
