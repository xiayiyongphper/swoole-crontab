<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-8-22
 * Time: 下午7:51
 */

namespace generator\models;

use framework\db\ActiveRecord;

/**
 * Class Orders
 * @package common\models
 * @property integer $qty
 * @property float $gmv
 * @property integer $entity_id
 * @property string $city_name
 * @property integer $city
 * @property string $day
 *
 */
class Orders extends ActiveRecord
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    protected $parent_id;
    protected $seed;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('testDb');
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param mixed $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return mixed
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * @param mixed $seed
     */
    public function setSeed($seed)
    {
        $this->seed = $seed;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray();
        $data['parent_id'] = $this->parent_id;
        $data['seed'] = $this->seed;
        return $data;
    }
}