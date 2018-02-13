<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/8/30
 * Time: 19:55
 */

namespace service\processes;


use common\models\common\CrontabMQMsg;
use framework\components\es\Console;
use framework\components\ToolsAbstract;
use framework\core\ProcessInterface;
use framework\core\SWServer;
use framework\message\Message;
use framework\mq\MQAbstract;
use PhpAmqpLib\Message\AMQPMessage;
use service\message\common\Header;
use service\message\common\Protocol;
use service\message\common\SourceEnum;

/**
 * Class InitProcess
 * @package service\processes
 */
class MQProcess implements ProcessInterface
{
    private $server;
    private $process;
    /**
     * @var array
     */
    private $clients = [];

    private $header;
    private $max = 5;
    private $inc = 11;
    /**
     * 绑定关系。加一个新的请更新$clientMap。
     * @var array
     */
    private $bindingKeys = [
        MQAbstract::MSG_ORDER_CREATE,
        MQAbstract::MSG_ORDER_UPDATE,
        MQAbstract::MSG_ORDER_CANCEL,
        MQAbstract::MSG_ORDER_AGREE_CANCEL,
        MQAbstract::MSG_ORDER_PENDING_COMMENT,
        MQAbstract::MSG_ORDER_COMMENT,
        MQAbstract::MSG_ORDER_REBATE_SUCCESS,
        MQAbstract::MSG_ORDER_REJECTED_CLOSED,
        MQAbstract::MSG_ORDER_CONFIRM,
        MQAbstract::MSG_ORDER_REJECT_CANCEL,
        MQAbstract::MSG_ORDER_APPLY_CANCEL,
        MQAbstract::MSG_ORDER_NEW,
        MQAbstract::MSG_ORDER_CLOSED,
        MQAbstract::MSG_ORDER_MANUAL_RETURN_COUPON,
        MQAbstract::MSG_ORDER_MANUAL_REBATE,
        MQAbstract::MSG_ORDER_MANUAL_RETURN_CHANGE,
        MQAbstract::MSG_PRODUCT_CREATE,
        MQAbstract::MSG_PRODUCT_UPDATE,
        MQAbstract::MSG_PRODUCT_DELETE,
        MQAbstract::MSG_CUSTOMER_CREATE,
        MQAbstract::MSG_CUSTOMER_LOGIN,
        MQAbstract::MSG_CUSTOMER_APPROVED,
        MQAbstract::MSG_MERCHANT_HOMEPAGE,
        MQAbstract::MSG_CUSTOMER_UPDATE,
        MQAbstract::MSG_MARKETING_CUSTOMER_PUSH,
        MQAbstract::MSG_SMS,  // 暂时没用
        MQAbstract::MSG_PUSH,    // customer用到
        MQAbstract::MSG_ROUTE,
        MQAbstract::MSG_MERCHANT_SECKILL_PUSH,
        MQAbstract::MSG_GROUP_SUB_PRODUCT_UPDATE,
        MQAbstract::MSG_MERCHANT_UPDATE_STORE
    ];

    /**
     * 默认的client ID
     */
    const DEFAULT_CLIENT_ID = 10000;

    /**
     * 客户端ID映射。
     * 目前消息都没有客户端ID，所以根据key来分割，获取最前面的字符串，然后根据映射关系获取ID。
     * 如：customer.login，获取到的是customer，然后获取ID
     * @var array
     */
    private $clientMap = [
        'merchant' => 10001,
        'route' => 10002,
        'customer' => 10003,
        'contractor' => 10004,
        'core' => 10005,
        'product' => 10101,
        'order' => 10102,
    ];

    const LOG_FILE = 'mq_process.log';

    /**
     * @inheritdoc
     */
    public function run(SWServer $SWServer, \swoole_process $process)
    {
        $this->server = $SWServer;
        $this->process = $process;
        try {
            ToolsAbstract::getMQ(true)->consume(function ($msg) {
                try {
                    // 防重
                    if (++$this->inc >= 100) {
                        $this->inc = 10;
                    }

                    /** @var  AMQPMessage $msg */
                    $body = json_decode($msg->body, true);
                    // 记录到文件
                    MQProcess::log($body);
                    // 保存消息到数据库
                    if ($msgId = $this->saveMsg($body)) {
                        MQProcess::log('__msg_id__=' . $msgId);
                        $body['__msg_id__'] = $msgId;  // 增加__msg_id__字段
                    }
                    // 发送消息到定时任务系统
                    $client = $this->getClient();
                    $client->send(Message::pack($this->getHeader(), $body));
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    $this->receive();
                } catch (\Exception $e) {
                    ToolsAbstract::logException($e);
                    $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], true);
                } catch (\Error $error) {
                    ToolsAbstract::logError($error);
                    $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], true);
                }
            }, null, $this->bindingKeys);
        } catch (\Exception $e) {
            sleep(10);
            ToolsAbstract::logException($e);
        }
    }

    private function getClientCount()
    {
        return count($this->clients);
    }

    private function getHeader()
    {
        if (!$this->header) {
            $header = new Header();
            $header->setSource(SourceEnum::CRONTAB);
            $header->setRoute('taskCommon.MQProcessor');
            $header->setProtocol(Protocol::JSON);
            $header->setVersion(1);
            $this->header = $header;
        }
        return $this->header;
    }

    private function getClient()
    {
        $client = new \swoole_client(SWOOLE_SOCK_UNIX_STREAM, SWOOLE_SOCK_SYNC);
        $clientConfig = \Yii::$app->params['soa_client_config'];
        $client->set($clientConfig);
        $fp = $client->connect(\Yii::$app->params['ip_port']['host'], ENV_SERVER_PORT);
        if (!$fp) {
            $this->log("Error:{$fp->errMsg} {$fp->errCode}");
            return false;
        }
        $this->clients[$client->sock] = $client;
        return $client;
    }

    private function receive()
    {
        if (!empty($this->clients)) {
            $write = $error = array();
            $read = array_values($this->clients);
            $n = swoole_client_select($read, $write, $error, 0.6);
            if ($n > 0) {
                /**
                 * @var integer $index
                 * @var \swoole_client $c
                 */
                foreach ($read as $index => $c) {
                    try {
                        $data = $c->recv();
                        $message = new Message();
                        $message->unpackResponse($data);
                        $body = $message->getPackageBody();
                        $this->log("Recv #{$c->sock}: " . $body . "\n");
                        unset($this->clients[$c->sock]);
                    } catch (\Exception $e) {
                        if ($c->errCode != 11) {
                            ToolsAbstract::log($c, 'exception.log');
                            ToolsAbstract::logException($e);
                        }
                    } catch (\Error $error) {
                        ToolsAbstract::logError($error);
                    }
                }
            }
        }
    }

    /**
     * 保存消息。
     * 目前消息都没有传客户端的消息，所以都是自动生成的。
     *
     * @param string $msg
     * @return int
     */
    private function saveMsg($msg)
    {
        if (empty($msg['key']) || !($arr = preg_split('/[-_.]+/', $msg['key']))) {
            return 0;
        }

        $client = $arr[0];
        $msgModel = new CrontabMQMsg();
        $msgModel->client_id = isset($this->clientMap[$client]) ? $this->clientMap[$client] : static::DEFAULT_CLIENT_ID;
        $msgModel->client_msg_id = ToolsAbstract::getDate()->date('YmdHis') . rand(1000, 9999) . $this->inc;
        $msgModel->human_msg_id = $msgModel->client_id . '_' . $msgModel->client_msg_id;
        $msgModel->origin_data = json_encode((array)$msg);
        $msgModel->consumer = ENV_SERVER_IP;
        $msgModel->status = 1;
        $msgModel->notes = '';
        if ($msgModel->save()) {
            return $msgModel->entity_id;
        } else {
//            $this->log($msgModel->errors);
            return 0;
        }
    }

    private static function log($data)
    {
        ToolsAbstract::log($data, MQProcess::LOG_FILE);
    }
}
