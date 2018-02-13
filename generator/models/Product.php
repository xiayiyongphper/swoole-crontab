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
 * Class Products
 * @package common\models
 * @property float $price
 * @property integer $qty
 * @property string $barcode
 * @property integer $entity_id
 * @property integer $product_id
 * @property integer $wholesaler_id
 * @property float $single_gross_profit
 * @property float $gross_profit_rate
 * @property float $gross_profit
 * @property string $month
 * @property integer $city
 * @property string $city_name
 * @property float $gmv
 *
 */
class Product extends ActiveRecord
{
    protected $used = 0;
    protected $selected = 0;
    public $name;
    public $first_category_id;
    public $second_category_id;
    public $third_category_id;
    public $brand;
    public $image;
    public $specification;
    public $origin;
    public $production_date;
    public $delta = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('testDb');
    }

    /**
     * @return int
     */
    public function getUsed(): int
    {
        return $this->used;
    }

    /**
     * @param int $used
     */
    public function setUsed(int $used)
    {
        $this->used = $used;
    }

    /**
     * @return int
     */
    public function getSelected(): int
    {
        return $this->selected;
    }

    /**
     * @param int $selected
     */
    public function setSelected(int $selected)
    {
        $this->selected = $selected;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray();
        $data['selected'] = $this->selected;
        $data['used'] = $this->used;
        $data['name'] = $this->name;
        $data['first_category_id'] = $this->first_category_id;
        $data['second_category_id'] = $this->second_category_id;
        $data['third_category_id'] = $this->third_category_id;
        $data['brand'] = $this->brand;
        $data['image'] = $this->image;
        $data['specification'] = $this->specification;
        $data['origin'] = $this->origin;
        $data['production_date'] = $this->production_date;
        return $data;
    }

}