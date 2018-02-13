<?php
namespace common\models;

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
class LelaiUserVisitScoreResult extends ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('resultDb');
    }

    public static function tableName()
    {
        return 'lelai_user_visit_score_result';
    }

}
