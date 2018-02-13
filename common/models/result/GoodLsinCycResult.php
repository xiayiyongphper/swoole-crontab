<?php
/**
 * Created by PhpStorm.
 * User: Ryan Hong
 * Date: 2017/11/29
 * Time: 10:32
 */

namespace common\models\result;

use framework\db\ActiveRecord;
//use Yii;

/**
 * Class GoodLsinCycResult
 * @property integer $entity_id
 * @property string $username
 * @property integer $province
 * @property integer $city
 */
class GoodLsinCycResult extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lelai_good_lsin_cyc_result';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('resultDb');
    }
}