<?php
/**
 * Created by PhpStorm.
 * User: Ryan Hong
 * Date: 2017/11/30
 * Time: 14:17
 */

namespace common\models\customer;

use framework\db\ActiveRecord;
use framework\components\ToolsAbstract;

/**
 * Class BestSellingLsin7Days
 * @package common\models\customer
 * @property string $lsin
 * @property integer $order_num
 * @property integer $first_category_id
 * @property integer $second_category_id
 * @property integer $third_category_id
 * @property string $brand
 * @property string $created_at
 * @property string $updated_at
 */
class BestSellingLsin7Days extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'best_selling_lsin_7_days';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('customerDb');
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $curDateTime = ToolsAbstract::getDate()->date();
        if ($insert) {
            $this->created_at = $curDateTime;
        }
        $this->updated_at = $curDateTime;

        return parent::beforeSave($insert);
    }
}