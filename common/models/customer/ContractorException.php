<?php
/**
 * Created by Jason.
 * Author: Jason Y. Wang
 * Date: 2016/1/25
 * Time: 13:35
 */

namespace common\models\customer;


use yii\base\Exception;

class ContractorException extends Exception
{

    const CUSTOMER_PHONE_ALREADY_REGISTERED = 12005;
    const CUSTOMER_PHONE_ALREADY_REGISTERED_TEXT = '该手机号码已注册！';

    const CONTRACTOR_INIT_ERROR = 39001;
    const CONTRACTOR_INIT_ERROR_TEXT = '业务员不存在';

    const CONTRACTOR_PERMISSION_ERROR = 39004;
    const CONTRACTOR_PERMISSION_ERROR_TEXT = '无权访问该模块';

    const CONTRACTOR_CITY_LIST_EMPTY = 39005;
    const CONTRACTOR_CITY_LIST_EMPTY_TEXT = '业务员名下城市为空';

    const FIELD_SPECIAL_CHARACTER = 39006;
    const FIELD_SPECIAL_CHARACTER_TEXT = '不能含有特殊字符';

    const SERVICE_NOT_AVAILABLE = 40001;
    const SERVICE_NOT_AVAILABLE_TEXT = '系统错误，请稍后重试！';

    const MARK_PRICE_PRODUCT_NOT_FOUND = 40004;
    const MARK_PRICE_PRODUCT_NOT_FOUND_TEXT = '找不到该商品';

    const CONTRACTOR_PHONE_INVALID = 42004;
    const CONTRACTOR_PHONE_INVALID_TEXT = '手机号码有误！';

    const CONTRACTOR_CODE_SEND_ERROR = 42003;
    const CONTRACTOR_CODE_SEND_ERROR_TEXT = '验证码发送错误！';

    const CONTRACTOR_NOT_FOUND = 42001;
    const CONTRACTOR_NOT_FOUND_TEXT = '业务员不存在';

    const CONTRACTOR_SMS_TYPE_INVALID = 42009;
    const CONTRACTOR_SMS_TYPE_INVALID_TEXT = '短信验证码类型错误!';

    const VERIFY_CODE_ALREADY_SEND = 42016;
    const VERIFY_CODE_ALREADY_SEND_TEXT = '验证码已经发送';

    const VERIFY_CODE_ALREADY_EXPIRED = 42017;
    const VERIFY_CODE_ALREADY_EXPIRED_TEXT = '验证码已过期';

    const VERIFY_CODE_ERROR = 42011;
    const VERIFY_CODE_ERROR_TEXT = '验证码错误!';

    const CONTRACTOR_AUTH_TOKEN_EXPIRED = 42002;
    const CONTRACTOR_AUTH_TOKEN_EXPIRED_TEXT = '用户信息已过期，请重新登陆！';

    const CONTRACTOR_FOUND_DISABLED = 42006;
    const CONTRACTOR_FOUND_DISABLED_TEXT = '您的账号已停用，请联系运营人员';

    const CONTRACTOR_STOREINFO_FORBIDDEN = 42005;
    const CONTRACTOR_STOREINFO_FORBIDDEN_TEXT = '无法查看该超市详情！';


    const BUSINESS_LICENSE_NO_EXIST = 42100;
    const BUSINESS_LICENSE_NO_EXIST_TEXT = '此营业执照号已存在，无法重复添加！';
    const STORE_INTENTION_NOT_FOUND = 42101;
    const STORE_INTENTION_NOT_FOUND_TEXT = '未找到此意向店铺！';

    const CONTRACTOR_NOT_ALLOCATE_ROLE = 44009;
    const CONTRACTOR_NOT_ALLOCATE_ROLE_TEXT = '业务员未分配角色，请联系运营人员';


    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }

    public static function contractorPhoneInvalid()
    {
        throw new ContractorException(self::CONTRACTOR_PHONE_INVALID_TEXT, self::CONTRACTOR_PHONE_INVALID);
    }

    public static function contractorAuthTokenExpired()
    {
        throw new ContractorException(self::CONTRACTOR_AUTH_TOKEN_EXPIRED_TEXT, self::CONTRACTOR_AUTH_TOKEN_EXPIRED);
    }

    public static function contractorNotExist()
    {
        throw new ContractorException(self::CONTRACTOR_NOT_FOUND_TEXT, self::CONTRACTOR_NOT_FOUND);
    }

    public static function contractorDisabled()
    {
        throw new ContractorException(self::CONTRACTOR_FOUND_DISABLED_TEXT, self::CONTRACTOR_FOUND_DISABLED);
    }

    public static function contractorNotAllocateRole()
    {
        throw new ContractorException(self::CONTRACTOR_NOT_ALLOCATE_ROLE_TEXT, self::CONTRACTOR_NOT_ALLOCATE_ROLE);
    }

    public static function contractorSmsTypeInvalid()
    {
        throw new ContractorException(self::CONTRACTOR_SMS_TYPE_INVALID_TEXT, self::CONTRACTOR_SMS_TYPE_INVALID);
    }

    public static function verifyCodeAlreadySend()
    {
        throw new ContractorException(self::VERIFY_CODE_ALREADY_SEND_TEXT, self::VERIFY_CODE_ALREADY_SEND);
    }

    public static function verifyCodeAlreadyExpired()
    {
        throw new ContractorException(self::VERIFY_CODE_ALREADY_EXPIRED_TEXT, self::VERIFY_CODE_ALREADY_EXPIRED);
    }

    public static function verifyCodeError()
    {
        throw new ContractorException(self::VERIFY_CODE_ERROR_TEXT, self::VERIFY_CODE_ERROR);
    }

    public static function contractorSystemError()
    {
        throw new ContractorException(self::SERVICE_NOT_AVAILABLE_TEXT, self::SERVICE_NOT_AVAILABLE);
    }

    public static function contractorCodeSendError()
    {
        throw new ContractorException(self::CONTRACTOR_CODE_SEND_ERROR_TEXT, self::CONTRACTOR_CODE_SEND_ERROR);
    }

    public static function businessLicenseNoExist()
    {
        throw new ContractorException(self::BUSINESS_LICENSE_NO_EXIST_TEXT, self::BUSINESS_LICENSE_NO_EXIST);
    }

    public static function storeIntentionNotFound()
    {
        throw new ContractorException(self::STORE_INTENTION_NOT_FOUND_TEXT, self::STORE_INTENTION_NOT_FOUND);
    }

    public static function contractorStoreInfoForbidden()
    {
        throw new ContractorException(self::CONTRACTOR_STOREINFO_FORBIDDEN_TEXT, self::CONTRACTOR_STOREINFO_FORBIDDEN);
    }

    public static function markPriceProductNotFound()
    {
        throw new ContractorException(self::MARK_PRICE_PRODUCT_NOT_FOUND_TEXT, self::MARK_PRICE_PRODUCT_NOT_FOUND);
    }

    public static function contractorInitError()
    {
        throw new CustomerException(self::CONTRACTOR_INIT_ERROR_TEXT, self::CONTRACTOR_INIT_ERROR);
    }

    public static function contractorPermissionError()
    {
        throw new CustomerException(self::CONTRACTOR_PERMISSION_ERROR_TEXT, self::CONTRACTOR_PERMISSION_ERROR);
    }

    public static function contractorCityListEmpty()
    {
        throw new CustomerException(self::CONTRACTOR_CITY_LIST_EMPTY_TEXT, self::CONTRACTOR_CITY_LIST_EMPTY);
    }

    public static function fieldSpecialCharacter()
    {
        throw new CustomerException(self::FIELD_SPECIAL_CHARACTER_TEXT, self::FIELD_SPECIAL_CHARACTER);
    }

    public static function customerPhoneAlreadyRegistered()
    {
        throw new CustomerException(self::CUSTOMER_PHONE_ALREADY_REGISTERED_TEXT, self::CUSTOMER_PHONE_ALREADY_REGISTERED);
    }

}