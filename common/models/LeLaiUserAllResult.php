<?php

namespace common\models;

use framework\db\ActiveRecord;
use Yii;

/**
 * Class Dimension
 * @package common\models
 * @property integer $entity_id
 * @property integer $type
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 *
 */
class LeLaiUserAllResult extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lelai_user_all_result';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('resultDb');
    }

}