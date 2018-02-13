<?php

namespace common\models;

use framework\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "region_area".
 *
 * @property integer $entity_id
 * @property integer $source
 * @property string $city
 * @property integer $district_id
 * @property integer $circle_id
 * @property string $area_name
 * @property string $area_address
 * @property string $lng
 * @property string $lat
 * @property string $whole_spell
 * @property string $each_first_letter
 */
class RegionArea extends ActiveRecord
{

    public static function tableName()
    {
        return 'lelai_slim_common.region_area';
    }

    public static function getDb()
    {
        return Yii::$app->get('commonDb');
    }
}
