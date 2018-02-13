<?php

namespace common\models\customer;

use common\components\Events;
use framework\components\es\Console;
use framework\components\ProxyAbstract;
use framework\components\ToolsAbstract;
use framework\db\ActiveRecord;
use yii\db\Expression;

/**
 * LeCustomer model
 * @property integer $entity_id
 * @property string $username
 * @property integer $province
 * @property integer $city
 * @property integer $phone
 * @property integer $district
 * @property integer $area_id
 * @property string $address
 * @property string $detail_address
 * @property string $store_name
 * @property string $storekeeper
 * @property string $store_area
 * @property string $lat
 * @property string $lng
 * @property string $img_lat
 * @property string $img_lng
 * @property string $password_reset_token
 * @property string $email
 * @property string $contractor
 * @property string $auth_token
 * @property float $orders_total_price
 * @property integer $status
 * @property integer $type
 * @property integer $level
 * @property integer $contractor_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $apply_at
 * @property integer $first_order_id
 * @property string $first_order_at
 * @property string $business_license_img
 * @property string $store_front_img
 * @property string $password write-only password
 * @property string $new_password
 * @property float $balance
 * @property float $business_license_no
 * @property string $last_visited_at
 * @property string $last_place_order_at
 * @property string $storekeeper_instore_times
 * @property integer $last_place_order_id
 * @property float $last_place_order_total
 * @property integer $state
 * @property string $review_results
 * @property integer $is_login_white_list
 * @property integer $refresh_auth_token
 */
class LeCustomer extends ActiveRecord
{

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const STATUS_UNCHECKED = 0;
    const STATUS_PASSED = 1;
    const STATUS_NOT_PASSED = 2;

    /**
     * 不重新生成token
     */
    const REFRESH_AUTH_TOKEN_NO = 0;

    /**
     * 重新生成token
     */
    const REFRESH_AUTH_TOKEN_YES = 1;
    /**
     * 默认状态
     */
    const STATE_PENDING = 0;
    /**
     * 待客服审核
     */
    const STATE_PENDING_REVIEW = 1;
    /**
     * 人工审核通过
     */
    const STATE_MANUAL_APPROVED = 2;
    /**
     * 人工审核不通过
     */
    const STATE_MANUAL_DISAPPROVED = 3;
    /**
     * 系统审核通过
     */
    const STATE_AUTOMATIC_APPROVED = 4;

    const CUSTOMERS_INFO_COLLECTION = 'customers_info_collection';
    public $auth_key;

    public $distance;
    protected $customer_style = 0;

    // 余额每天消费限额
    const BALANCE_CONSUME_LIMIT = 500;

    /**
     * 默认设备最大登录账号数
     */
    const DEFAULT_MAX_LOGIN_ACCOUNT_COUNT_PER_DEVICE = 9999;

    /**
     * 默认设备最大注册账号数
     */
    const DEFAULT_MAX_REG_ACCOUNT_COUNT_PER_DEVICE = 9999;

    /*
     * 特殊deviceId,这些deviceId登录注册不受限制
     */
    const DEVICE_ID_WHITE_LIST = [
        '000000000000000',
        'ma02:00:00:00:00:00',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'le_customers';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('customerDb');
    }

    /**
     * 通过userId得到超市模型
     * @param $customerId
     * @return null|static
     */
    public static function findByCustomerId($customerId)
    {
        return static::findOne(['entity_id' => $customerId]);
    }

    /**
     * 增加余额
     * @param string $title
     * @param string $action
     * @param int $amount
     * @param null $order_id
     * @param null $order_no
     *
     * @return bool
     */
    public function addBalance($title = 'UNKNOW', $action = 'UNDEFINED', $amount = 0, $order_id = null, $order_no = null)
    {
        ToolsAbstract::log('addBalance', 'balance.txt');
        $customer_id = $this->entity_id;
        if (!$customer_id) {
            return false;
        }

        if ($amount < 0) {
            return false;
        }

        ToolsAbstract::log([
            $customer_id,
            $title,
            $action,
            1,
            $amount,
            $this->balance + $amount,
            $order_id, $order_no
        ], 'balance.txt');

        $this->addBalanceLog($customer_id, $title, $action, 1, $amount, $this->balance + $amount, $order_id, $order_no);

        $this->setAttribute('balance', $this->balance + $amount);
        if (!$this->save()) {
            Console::get()->log(
                [
                    'errors' => $this->errors,
                    'params' => [$customer_id, $title, $action, 1, $amount, $this->balance + $amount, $order_id, $order_no],
                ], null,
                ['error', 'balance', 'addBalance'],
                Console::ES_LEVEL_ERROR
            );
        }
        return true;
    }

    /**
     * @param null $transaction_no
     * @param null $customer_id
     * @param string $title
     * @param string $action
     * @param int $type
     * @param int $amount
     * @param null $balance
     * @param null $order_id
     * @param null $order_no
     *
     * @return string
     */
    public function addBalanceLog(
        $customer_id = null,
        $title = 'UNKNOW',
        $action = 'UNDEFINED',
        $type = 1,
        $amount = 0,
        $balance = null,
        $order_id = null,
        $order_no = null)
    {
        // 交易号
        $transaction_no = LeCustomersBalanceLog::getTransactionNo();

        /** @var LeCustomersBalanceLog $bLog */
        $bLog = new LeCustomersBalanceLog();
        $data = [
            'transaction_no' => $transaction_no,
            'customer_id' => $customer_id,
            'title' => $title,
            'action' => $action,
            'type' => $type,
            'amount' => $amount,
            'balance' => $balance,
            'order_id' => $order_id,
            'order_no' => $order_no,
            'created_at' => date('Y-m-d H:i:s', time()),
        ];
        $bLog->setAttributes($data, false);
        ToolsAbstract::log('addBalanceLog', 'balance.txt');
        ToolsAbstract::log($bLog->toArray(), 'balance.txt');
        if (!$bLog->insert(false)) {
            ToolsAbstract::log($bLog->errors, 'balance.txt');
            Console::get()->log(
                [
                    'data' => $data,
                    'errors' => $bLog->errors,
                ], null,
                ['balance', 'addBalanceLog'],
                Console::ES_LEVEL_ERROR
            );
        } else {
            Console::get()->log($data, null, ['balance', 'addBalanceLog'], Console::ES_LEVEL_NOTICE);
        }

        // 在增加balanceLog的同时也新增一条记录到订单状态里
        // 与订单相关的才通知core模块
        if ($order_id) {
            // 现在只有取消退回钱包的才发消息给core
            if ($action == 'RETURN') {
                // Comment
                $data['comment'] = '已退回¥' . $amount . '到零钱';

                $name = Events::EVENT_BALANCE_CHANGE;
                $eventName = Events::getCoreEventName($name);
                $events[$eventName] = [
                    'name' => $name,
                    'data' => $data,
                ];
                foreach ($events as $eventName => $event) {
                    ProxyAbstract::sendMessage($eventName, $event);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     *系统判断附近500m范围内没有同名超市（超市填写的店名完全相同，过滤掉特殊字符、阿拉伯数字、字母）。
     * @return array
     */
    public function getDuplicatedStores()
    {
        $distance = 0.5;//500米
        $filteredName = $this->store_name;
        //去掉数字、字母、特殊字符
        $lat = 0;
        if ($this->lat) {
            $lat = $this->lat;
        }

        $lng = 0;
        if ($this->lng) {
            $lng = $this->lng;
        }

        $filteredName = preg_replace('/[\d\w\s]/', '', $filteredName);
        $query = self::find()->addSelect(['entity_id', 'store_name'])
            ->where(new Expression('ACOS(SIN((' . $lat . ' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS((' . $lat . ' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS((' . $lng . ' * 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380 <= ' . $distance))
            ->andWhere(['store_name' => $filteredName])
            ->andWhere(['<>', 'entity_id', $this->entity_id]);
        ToolsAbstract::log($query->createCommand()->getRawSql());
        $customer = $query->asArray()
            ->all();

        return $customer;
    }

    /**
     * 增加额度包余额
     * @param string $title
     * @param string $action
     * @param int $amount
     * @param null $order_id
     * @param null $order_no
     */
    public function addBalanceAdditionalPackage($title = 'UNKNOW', $action = 'UNDEFINED', $amount = 0, $order_id = null, $order_no = null)
    {
        ToolsAbstract::log('addBalanceAdditionalPackage', 'additional_package.txt');
        $customer_id = $this->getId();
        if (!$customer_id) {
            return false;
        }
        if ($amount < 0) {
            return false;
        }

        // 用户当前额度包余额
        $ap = $this->getAdditionalPackage();
        ToolsAbstract::log(
            [$customer_id, $title, $action, 1, $amount, $ap + $amount, $order_id, $order_no],
            'additional_package.txt'
        );
        $this->addBalanceAdditionalPackageLog($customer_id, $title, $action, 1, $amount, $ap + $amount, $order_id, $order_no);

        // 保存额度包余额
        /** @var LeCustomersBalanceAdditionalPackage $bap */
        $bap = LeCustomersBalanceAdditionalPackage::findByCustomerId($this->getId());
        if (!$bap) {
            // 还未建立记录
            $bap = new LeCustomersBalanceAdditionalPackage();
            $bap->customer_id = $customer_id;
        }
        // 更新
        $bap->setAttribute('balance', $ap + $amount);

        if (!$bap->save()) {
            Console::get()->log(
                [
                    'errors' => $bap->errors,
                    'params' => [$customer_id, $title, $action, 1, $amount, $ap + $amount, $order_id, $order_no],
                ], null,
                ['error', 'database_error', 'balance_additional_package', 'addBalanceAdditionalPackage'],
                Console::ES_LEVEL_ERROR
            );
            return false;
        }

        return true;
    }

    /**
     * @param null $transaction_no
     * @param null $customer_id
     * @param string $title
     * @param string $action
     * @param int $type
     * @param int $amount
     * @param null $balance
     * @param null $order_id
     * @param null $order_no
     *
     * @return string
     */
    public function addBalanceAdditionalPackageLog(
        $customer_id = null,
        $title = 'UNKNOW',
        $action = 'UNDEFINED',
        $type = 1,
        $amount = 0,
        $balance = null,
        $order_id = null,
        $order_no = null)
    {
        // 交易号
        $transaction_no = LeCustomersBalanceLog::getTransactionNo();

        /** @var LeCustomersBalanceAdditionalPackageLog $bLog */
        $bLog = new LeCustomersBalanceAdditionalPackageLog();
        $data = [
            'transaction_no' => $transaction_no,
            'customer_id' => $customer_id,
            'title' => $title,
            'action' => $action,
            'type' => $type,
            'amount' => $amount,
            'balance' => $balance,
            'order_id' => $order_id,
            'order_no' => $order_no,
            'created_at' => date('Y-m-d H:i:s', time()),
        ];
        $bLog->setAttributes($data, false);
        ToolsAbstract::log('addBalanceAdditionalPackageLog', 'additional_package.txt');
        ToolsAbstract::log($bLog->toArray(), 'additional_package.txt');
        if (!$bLog->insert(false)) {
            Console::get()->log(
                [
                    'data' => $data,
                    'errors' => $bLog->errors,
                ], null,
                ['balance_additional_package', 'addBalanceAdditionalPackageLog'],
                Console::ES_LEVEL_ERROR
            );
        } else {
            Console::get()->log($data, null, ['balance_additional_package', 'addBalanceAdditionalPackageLog'], Console::ES_LEVEL_NOTICE);
        }
    }

    /**
     * 减少余额
     * @param string $title
     * @param string $action
     * @param int $amount
     * @param null $order_id
     * @param null $order_no
     */
    public function reduceBalanceAdditionalPackage($title = 'UNKNOW', $action = 'UNDEFINED', $amount = 0, $order_id = null, $order_no = null)
    {
        ToolsAbstract::log('reduceBalanceAdditionalPackage', 'additional_package.txt');
        $customer_id = $this->getId();
        if (!$customer_id) {
            return;
        }
        if ($amount < 0) {
            return;
        }

        // 用户当前额度包余额
        $ap = $this->getAdditionalPackage();
        ToolsAbstract::log(
            [$customer_id, $title, $action, 0, $amount, $ap - $amount, $order_id, $order_no],
            'additional_package.txt'
        );
        $this->addBalanceAdditionalPackageLog($customer_id, $title, $action, 0, $amount, $ap - $amount, $order_id, $order_no);

        // 保存额度包余额
        $bap = LeCustomersBalanceAdditionalPackage::findByCustomerId($this->getId());
        if (!$bap) {
            // 还未建立记录
            $bap = new LeCustomersBalanceAdditionalPackage();
            $bap->customer_id = $customer_id;
        }
        $bap->setAttribute('balance', $ap - $amount);

        if (!$bap->save()) {
            Console::get()->log(
                [
                    'errors' => $this->errors,
                    'params' => [$customer_id, $title, $action, 0, $amount, $ap - $amount, $order_id, $order_no],
                ], null,
                ['balance_additional_package', 'reduceBalanceAdditionalPackage'],
                Console::ES_LEVEL_ERROR
            );
        }
    }

    /**
     * @return bool|int|string
     */
    public function getAdditionalPackage()
    {
        if (!$this->getId()) {
            return false;
        }
        return LeCustomersBalanceAdditionalPackage::getByCustomerId($this->getId());
    }

    /**
     * 减少余额
     * @param string $title
     * @param string $action
     * @param int $amount
     * @param null $order_id
     * @param null $order_no
     */
    public function reduceBalance($title = 'UNKNOW', $action = 'UNDEFINED', $amount = 0, $order_id = null, $order_no = null)
    {
        ToolsAbstract::log('reduceBalance', 'balance.txt');
        $customer_id = $this->getId();
        if (!$customer_id) {
            return;
        }
        if ($amount < 0) {
            return;
        }

        ToolsAbstract::log(
            [$customer_id, $title, $action, 0, $amount, $this->balance - $amount, $order_id, $order_no],
            'balance.txt'
        );
        $this->addBalanceLog($customer_id, $title, $action, 0, $amount, $this->balance - $amount, $order_id, $order_no);
        $this->setAttribute('balance', $this->balance - $amount);
        if (!$this->save()) {
            Console::get()->log(
                [
                    'errors' => $this->errors,
                    'params' => [$customer_id, $title, $action, 0, $amount, $this->balance - $amount, $order_id, $order_no],
                ], null,
                ['balance', 'reduceBalance'],
                Console::ES_LEVEL_ERROR
            );
        }
    }
}