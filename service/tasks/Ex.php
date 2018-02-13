<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/9/28
 * Time: 17:03
 */

namespace service\tasks;

/**
 * 异常。
 *
 * 每个小模块/Service先增加一个BASE，然后递增，BASE都放在前面，每个小模块/Service32个code
 * @package service\tasks
 */
class Ex extends \Exception
{
    /* 基准CODE */
    const EX_BASE = 0x100000;

    /* common 部分 */
    const EX_COMMON_BASE = self::EX_BASE + 0x10000;
    const EX_OFFER_TRIGGER_BASE = self::EX_COMMON_BASE + 0x10;
    const EX_GENERATE_BASE = self::EX_COMMON_BASE + 0x30;
    const EX_PUSH_BASE = self::EX_COMMON_BASE + 0x50;

    /* contractor 部分 */
    const EX_CONTRACTOR_BASE = self::EX_BASE + 0x20000;

    /* merchant 部分 */
    const EX_MERCHANT_BASE = self::EX_BASE + 0x30000;

    /* core 部分 */
    const EX_CORE_BASE = self::EX_BASE + 0x40000;

    /* customer 部分 */
    const EX_CUSTOMER_BASE = self::EX_BASE + 0x50000;

    /**
     * @var array
     */
    private static $codeMap = [
        self::EX_BASE => '系统发生了异常',
        self::EX_OFFER_TRIGGER_BASE => '优惠触发发生了异常'
    ];

    /**
     * Ex constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param int $code
     * @param string $defaultMsg
     * @return mixed
     */
    public static function msg($code, $defaultMsg = '系统发生了异常')
    {
        if (isset(static::$codeMap[$code])) {
            return static::$codeMap[$code];
        }
        return $defaultMsg;
    }

    /**
     * @param int $code
     * @param string $msg
     * @return Ex
     */
    public static function getException($code, $msg = null)
    {
        if ($msg == null) {
            $msg = Ex::msg($code);
        }
        return new Ex($msg, $code);
    }
}