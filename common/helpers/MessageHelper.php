<?php

/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/9/5
 * Time: 15:57
 */
namespace common\helpers;

use common\models\Crontab;
use common\models\CrontabHistory;
use framework\components\ToolsAbstract;
use framework\message\Message;
use service\message\common\EncryptionMethod;
use service\message\common\Header;
use service\message\common\Protocol;
use service\message\common\SourceEnum;

/**
 * Class MessageHelper
 * @package common\helpers
 */
class MessageHelper
{
    const FROM_WORKER = CrontabHistory::FROM_WORKER;
    const FROM_RPC_INTERNAL = CrontabHistory::FROM_RPC_INTERNAL;
    const FROM_RPC_REMOTE = CrontabHistory::FROM_RPC_REMOTE;
    const FROM_CLI = CrontabHistory::FROM_CLI;

    /**
     * @param string $route
     * @param array $data
     * @return string
     */
    public static function pack($route, array $data)
    {
        $header = new Header();
        $header->setRoute($route);
        $header->setSource(SourceEnum::CRONTAB);
        $header->setProtocol(Protocol::JSON);
        $header->setEncrypt(EncryptionMethod::ORG);
        return Message::pack($header, $data);
    }

    /**
     * @param Crontab $job
     * @param string|int $from
     * @param array $data
     * @return string
     */
    public static function packJob(Crontab $job, $from = self::FROM_WORKER, array $data = [])
    {
        $data = array_merge(
            $data,
            [
                '@from' => $from,
                '@task_id' => $job->entity_id,
                '@scheduled_timestamp' => $job->scheduledTimestamp,
                '@timestamp' => ToolsAbstract::getDate()->timestamp(),
                '@params' => $job->params,
            ]
        );
        return static::pack($job->route, $data);
    }
}