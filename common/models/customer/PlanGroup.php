<?php
namespace common\models\customer;

use Yii;
use framework\db\ActiveRecord;

/**
 * VerifyCode model
 *
 * @property integer $entity_id
 * @property string $name
 * @property string $city
 * @property string $date
 * @property integer $status
 *
 */
class PlanGroup extends ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('customerDb');
    }

    public static function tableName()
    {
        return 'plan_group';
    }

}
