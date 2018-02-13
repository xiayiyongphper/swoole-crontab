<?php
namespace common\components;

use common\components\sms\SmsYp;
use common\components\sms\SmsYtx;
use common\models\LeContractor;
use common\models\LeCustomers;
use common\models\VerifyCode;
use framework\components\ToolsAbstract;
use service\message\customer\GetSmsRequest;
use service\models\common\ContractorException;
use service\models\common\CustomerException;

/**
 * Author: Jason Y. Wang
 * Class ContractorSms
 * @package common\components
 */
class ContractorSms extends Sms
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
                if (!LeContractor::findByPhone($request->getPhone())) {
                    ContractorException::contractorNotExist();
                }
                $type = self::CONTRACTOR_SMS_TYPE_LOGIN;
                $voice = false;
                break;
            case 2:  //快捷注册超市
                if (LeCustomers::findByPhone($request->getPhone())) {
                    CustomerException::customerPhoneAlreadyRegistered();
                }
                $type = self::CONTRACTOR_SMS_TYPE_REGISTER;
                $voice = false;
                break;
            default:
                ContractorException::contractorSmsTypeInvalid();
        }
        /** @var  VerifyCode $verify */
        $verify = VerifyCode::find()->where(['phone' => $request->getPhone(), 'verify_type' => $type])->orderBy(['created_at' => SORT_DESC])->one();
        if ($verify && strtotime($verify['created_at']) + 60 > time()) {
            ContractorException::verifyCodeAlreadySend();
        }else{
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
}