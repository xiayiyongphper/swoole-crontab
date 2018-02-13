<?php

namespace generator\models;

use framework\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "le_merchant_store".
 *
 * @property integer $entity_id
 * @property integer $merchant_id
 * @property string $user_name
 * @property string $password
 * @property string $store_name
 * @property string $customer_service_phone
 * @property string $shop_images
 * @property string $icon
 * @property string $logo
 * @property string $operate_time
 * @property string $deliver_region
 * @property string $min_trade_amount
 * @property integer $promised_delivery_time
 * @property string $contact_phone
 * @property integer $province
 * @property string $business_license_code
 * @property integer $city
 * @property integer $district
 * @property string $area_id
 * @property string $store_address
 * @property string $store_description
 * @property string $legal_representative_name
 * @property string $business_license_img
 * @property string $tax_registration_certificate_img
 * @property string $organization_code_certificate_img
 * @property string $business_category
 * @property string $lng
 * @property string $lat
 * @property string $created_at
 * @property string $updated_at
 * @property integer $is_info_complete
 * @property integer $status
 * @property integer $sort
 * @property float $rebates
 * @property string $marketing_tags
 * @property string $category_tags
 * @property string $store_category
 * @property mixed $commission
 * @property integer $store_type
 * @property integer $compensation_service
 *
 */
class LeMerchantStore extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'le_merchant_store';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('merchantDb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id',], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entity_id' => 'Merchant Store ID',
            'merchant_id' => 'Merchant ID',
            'user_name' => 'user name',
            'password' => '商家APP登陆密码',
            'store_name' => 'Stire name',
            'customer_service_phone' => '客服电话',
            'shop_images' => 'Shop Images',
            'icon' => 'store icon',
            'logo' => 'store logo',
            'operate_time' => '营业时间',
            'deliver_region' => '配送区域',
            'min_trade_amount' => '最小交易金额',
            'promised_delivery_time' => '承诺送达时间，单位小时，默认72小时。',
            'contact_phone' => '联系电话',
            'province' => '省份',
            'business_license_code' => '营业执照注册号',
            'city' => '所在城市',
            'district' => '区域',
            'area_id' => '片区ID,多个片区ID，以|左右分割',
            'store_address' => '店铺地址',
            'store_description' => '店铺描述',
            'legal_representative_name' => 'Legal Representative Name',
            'business_license_img' => '营业执照照片',
            'tax_registration_certificate_img' => '税务登记证图片',
            'organization_code_certificate_img' => '组织机构代码证图片',
            'business_category' => '经营品类',
            'lng' => 'Lng',
            'lat' => 'Lat',
            'created_at' => 'Creation Time',
            'updated_at' => 'Update Time',
            'is_info_complete' => '店铺信息是否填写完整',
            'status' => '状态0：未审核，1:正常营业,2:暂停营业，3：封号，4：审核不通过',
        ];
    }

}
