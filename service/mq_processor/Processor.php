<?php
/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午4:34
 */

namespace service\mq_processor;

use framework\components\es\ESLogger;
use framework\components\log\LogAbstract;
use framework\components\ToolsAbstract;

/**
 * Class Processor
 * @package service\mq_processor
 */
abstract class Processor
{
    /**
     * MQ消息ID
     * @var int
     */
    private $mqMsgId;
    /**
     * MQ的routing key也就是data里面的key
     * @var string
     */
    private $routingKey;
    /**
     * @var mixed
     */
    private $value;

    /** @var string 版本。如1.0/1_0 */
    private $version;

    /**
     * @param mixed $data 原始数据
     * @return mixed
     */
    public abstract function run($data);

    /**
     * MQ消息ID
     * @return int
     */
    public function getMqMsgId()
    {
        return $this->mqMsgId;
    }

    /**
     * 设置消息ID
     * @param int $mqMsgId
     * @return $this
     */
    public function setMqMsgId($mqMsgId)
    {
        $this->mqMsgId = $mqMsgId;
        return $this;
    }

    /**
     * MQ的routing key也就是data里面的key
     * @return string
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * MQ的routing key。
     * @param string $routingKey
     * @return Processor
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    /**
     * MQ事件的data里面的value
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * MQ事件的data里面的value
     * @param mixed $value
     * @return Processor
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @param mixed $msg
     * @param int $level
     * @param bool $log2ES 是否也保存到ES
     * @param string $fileName 文件名
     */
    protected function log($msg, $level = LogAbstract::LEVEL_INFO, $log2ES = false, $fileName = null)
    {
        try {
            ToolsAbstract::log($msg, $fileName ? $fileName : (str_replace("\\", '_', get_called_class()) . '.log'));
            if ($log2ES) {
                $this->log2ES($msg, $level);
            }
        } catch (\Exception $e) {
            // nothing
        }
    }

    /**
     * @param mixed $msg
     * @param int $level
     */
    protected function log2ES($msg, $level = LogAbstract::LEVEL_INFO)
    {
        try {
            ESLogger::get()->log($msg, $level);
        } catch (\Exception $e) {
            // nothing
        }
    }
}