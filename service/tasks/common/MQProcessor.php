<?php

namespace service\tasks\common;

/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-29
 * Time: 下午2:20
 */


use common\models\common\CrontabMQMsg;
use framework\components\es\Timeline;
use framework\components\log\LogAbstract;
use framework\components\ToolsAbstract;
use service\tasks\TaskService;

/**
 * 定时任务预先生成
 * @package service\tasks
 * @author zqy
 * @author zxj
 */
class MQProcessor extends TaskService
{
    /**
     * @inheritdoc
     */
    public function run($data)
    {
        $success = true;
        $timeStart = microtime(1);
        $key = ToolsAbstract::arrayGetString($data, 'key');
        $value = ToolsAbstract::arrayGet($data, 'value', []);
        $msgId = ToolsAbstract::arrayGetString($data, '__msg_id__');
        $version = ToolsAbstract::arrayGetString($data, 'version');

        try {
            // 记录消息到文件
            $this->log($data);
            // 更新消息
            CrontabMQMsg::trace($msgId, ['MQProcessor' => 'fetch']);
            // 初始化Processor并执行
            $class = $this->getResource($data['key'], $version);
            /** @var \service\mq_processor\Processor $processor */
            $processor = new $class();
            $processor->setRoutingKey($key)->setValue($value)->setMqMsgId($msgId)->setVersion($version);
            $processor->run($data);
            return ['success' => true, '__msg_id__' => $msgId];
        } catch (\Exception $e) {
            $success = false;
            $this->log($e->__toString(), LogAbstract::LEVEL_NOTICE, true, 'MQProcessor.log');
            ToolsAbstract::logException($e);
            throw $e;
        } catch (\Error $e) {
            $success = false;
            ToolsAbstract::logError($e);
            throw $e;
        } finally {
            try {
                // 更新消息
                if ($success) {
                    CrontabMQMsg::trace($msgId, ['MQProcessor' => 'success']);
                } else {
                    CrontabMQMsg::trace($msgId, ['MQProcessor' => 'failed'], 99);
                }

                $this->log('finish!__msg_id__=' . $msgId . ',res=' . (int)$success);

                // 上报到ES
                $elapsed = microtime(1) - $timeStart;
                $traceId = sprintf('%s-%s', $msgId, $version);
                Timeline::get()->report($key, __FUNCTION__, ENV_SYS_NAME, $elapsed, ($success ? 0 : 1), $traceId, 0);
            } catch (\Throwable $throwable) {
                // nothing to do
            }
        }
    }

    /**
     * @param string $key
     * @param string $version
     * @return string
     * @throws \Exception
     */
    protected function getResource($key, $version)
    {
        $parts = explode('.', $key);
        if (!$parts || !is_array($parts)) {
            throw new \Exception('invalid route');
        }

        if (count($parts) === 1) {
            array_unshift($parts, 'service\mq_processor', '_default'); // 没有就用_default
        } else {
            array_unshift($parts, 'service\mq_processor');
        }

        $className = '';
        $array = explode('_', array_pop($parts));
        foreach ($array as $tmp) {
            $className .= ucfirst($tmp);
        }

        if (!empty($version)) {
            array_push($parts, 'v' . str_replace('.', '_', $version));
        }

        array_push($parts, $className . 'Processor');

        return implode('\\', $parts);
    }

    /**
     * @inheritdoc
     */
    protected function log($msg, $level = LogAbstract::LEVEL_INFO, $log2ES = false, $fileName = null)
    {
        parent::log($msg, $level, $log2ES, 'MQProcessor.log');
    }
}
