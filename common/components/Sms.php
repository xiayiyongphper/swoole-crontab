<?php
/**
 * Created by PhpStorm.
 * User: Jason Y. wang
 * Date: 17-2-17
 * Time: 下午3:52
 */

namespace common\components;

use common\components\sms\SmsYp;
use common\components\sms\SmsYtx;
use framework\components\ToolsAbstract;

class Sms
{
    const SMS_SEND_COUNT = 'sms_send_count';
    //云片
    const SMS_CHANNEL_YP = 0;
    //云通讯
    const SMS_CHANNEL_YTX = 1;

    /****************** 业务员 *************/
    //快捷登录
    const CONTRACTOR_SMS_TYPE_LOGIN = 41;
    //快捷注册
    const CONTRACTOR_SMS_TYPE_REGISTER = 42;

    /****************** 司机 *************/
    //快捷登录
    const DRIVER_SMS_TYPE_LOGIN = 51;

    //确认取消短信
    const DRIVER_SMS_TYPE_ORDER_CANCELED = 52;


    /****************** 订货网 *************/
    //注册
    const CUSTOMER_SMS_TYPE_REGISTER = 1;
    //忘记密码
    const CUSTOMER_SMS_TYPE_FORGET = 2;
    //快速登录
    const CUSTOMER_SMS_TYPE_LOGIN = 3;
    //收货码
    const CUSTOMER_SMS_TYPE_RECEIPT = 4;
    //微信退款通知
    const CUSTOMER_SMS_TYPE_REFUND = 5;
    //用户注册成功短信
    const CUSTOMER_SMS_TYPE_REGISTER_SUCCESS = 6;
    //修改用户绑定手机号
    const CUSTOMER_SMS_TYPE_CHANGE_BINDING_PHONE = 7;
    //修改用户绑定手机号
    const CUSTOMER_SMS_TYPE_STAFF_FORGET_PASSWORD = 8;

    //发送短信token
    const SMS_TOKEN = '9gdgtq7eym0579dobesmqm5ze0ig3mpm';

    /**
     * @param $phone
     * @param $type
     * @param $data
     * @param bool $voice
     * Author Jason Y. wang
     * 短信,根据发送次数做分发
     * @return bool
     */
    public static function send($phone, $type, $data, $voice = false)
    {
        $redis = ToolsAbstract::getRedis();
        $hashKey = $phone . '_' . $type;
        $count = $redis->hGet(self::SMS_SEND_COUNT, $hashKey);
        $channel = $count % 2;
        ToolsAbstract::log('channel:' . $channel, 'wangyang.log');
        switch ($channel) {
            case self::SMS_CHANNEL_YP:
                $sms = new SmsYp();
                break;
            case self::SMS_CHANNEL_YTX:
                $sms = new SmsYtx();
                $data = array_values($data);
                break;
            default:
                $sms = new SmsYp();
                break;
        }
        $result = $sms->send($phone, $type, $data, $voice);
        $redis->hSet(self::SMS_SEND_COUNT, $hashKey, $count + 1);
        return $result;
    }
}