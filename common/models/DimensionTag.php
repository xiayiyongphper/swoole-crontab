<?php
namespace common\models;

use framework\db\ActiveRecord;
use Yii;

/**
 * Class Dimension
 * @package common\models
 * @property integer $entity_id
 * @property string $name
 * @property integer $dimension_id
 * @property integer $status
 * @property integer $operator
 * @property string $created_at
 * @property string $updated_at
 *
 */
class DimensionTag extends ActiveRecord
{

    const STATUS_DISABLED = 2;
    const STATUS_ENABLED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dimension_tag';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('cmsDb');
    }

}