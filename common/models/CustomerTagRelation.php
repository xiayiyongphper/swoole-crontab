<?php

namespace common\models;

use Yii;


class CustomerTagRelation extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'customer_tag_relation';
    }

    public static function getDb()
    {
        return Yii::$app->get('customerDb');
    }
}
