<?php
namespace common\models\customer;

use Yii;
use framework\db\ActiveRecord;

/**
 * VerifyCode model
 *
 * @property integer $entity_id
 * @property integer $customer_id
 * @property string $date
 * @property integer $status
 *
 */
class VisitPlan extends ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('customerDb');
    }

    public static function tableName()
    {
        return 'visit_plan';
    }

}
