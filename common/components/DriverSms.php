<?php
namespace common\components;

use common\components\sms\SmsYp;
use common\components\sms\SmsYtx;
use common\models\customer\driver\Driver;
use common\models\customer\driver\Order;
use common\models\customer\VerifyCode;
use framework\components\ToolsAbstract;
use service\message\customer\GetSmsRequest;
use common\models\customer\ContractorException;
use common\models\customer\driver\DriverException;

/**
 * Author: Jason Y. Wang
 * Class DriverSms
 * @package common\components
 */
class DriverSms extends Sms
{

    /**
     * Function: sendMessage
     * Author: Jason Y. Wang
     *
     * @param GetSmsRequest $request
     * @return string
     * @throws ContractorException
     */
    public static function sendMessage(GetSmsRequest $request)
    {
        $voice = false;
        $type = 1;

        if (!preg_match('/1[34578]{1}\d{9}$/', $request->getPhone())) {
            ContractorException::contractorPhoneInvalid();
        }
        switch ($request->getType()) {
            case 1:  //快捷登录
                if (!Driver::findByPhone($request->getPhone())) {
                    DriverException::driverNotExist();
                }
                $type = DriverSms::DRIVER_SMS_TYPE_LOGIN;
                $voice = false;
                break;
            default:
                DriverException::driverSmsTypeInvalid();
        }

        /** @var  VerifyCode $verify */
        $verify = VerifyCode::find()->where(['phone' => $request->getPhone(), 'verify_type' => $type])->orderBy(['created_at' => SORT_DESC])->one();
        if ($verify && strtotime($verify['created_at']) + 60 > time()) {
            DriverException::verifyCodeAlreadySend();
        } else {
            $code = strrev(rand(1000, 9999));
            $verify = new VerifyCode();
            $verify->phone = $request->getPhone();
            $verify->code = $code;
            $verify->verify_type = $type;
            $verify->created_at = date('Y-m-d H:i:s');
            $verify->count = 1;
            $verify->save();
            self::send($request->getPhone(), $type, array('code' => $code, 'minute' => 1), $voice);
            return $code;
        }
    }

    public static function sendShortMessage($data)
    {
        $order_id = $data['order_id'];
        /** @var Order $order */
        $order = Order::getProcessingOrderByOrderId($order_id);
        ToolsAbstract::log($order_id, 'shortMessage.log');
        ToolsAbstract::log($order, 'shortMessage.log');
        if (!$order) {
            return false;
        }
        $driver_id = $order->driver_id;
        /** @var Driver $driver */
        $driver = Driver::findById($driver_id);
        if (!$driver) {
            return false;
        }

        $result = self::send($driver->phone, DriverSms::DRIVER_SMS_TYPE_ORDER_CANCELED,
            array('increment_id' => $order->increment_id, 'customer_name' => $order->customer_name));

        ToolsAbstract::log('#####result#######', 'shortMessage.log');
        ToolsAbstract::log($result, 'shortMessage.log');
        ToolsAbstract::log('#####result#######', 'shortMessage.log');
        return $result;
    }
}
