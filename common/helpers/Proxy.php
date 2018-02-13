<?php
/**
 * Created by PhpStorm.
 * User: ZQY
 * Date: 2017/10/10
 * Time: 14:32
 */

namespace common\helpers;

use framework\components\ToolsAbstract;
use framework\message\Message;
use service\message\common\Header;

/**
 * Class Proxy
 * 待完善，没时间测试，暂时弃用
 * @deprecated
 * @package common\helpers
 */
class Proxy
{
//    const KEY_LOCAL_SERVICE = 'local_service';
//    const KEY_INTERNAL_SERVICE = 'local.service';
//    const KEY_REMOTE_SERVICE = 'remote_service';
//    const KEY_HTTP_SERVICE = 'http_service';
//    const ROUTE_ROUTE_FETCH = 'route.fetch';
//    const ROUTE_ROUTE_REPORT = 'route.report';
//    const ROUTE_FETCH_TOKEN = 'yggBfivOTkMOFNDm';
//    const PING_TABLE = 'ping_table';
//    const SERVER_IP_SET = 'server_ip_set';
//    const SERVICE_PREFIX = 'service_';
//    const LOCAL = 'local';
//    const REMOTE = 'remote';
//    const HTTP = 'http';
//    const ROUTE_ALL_SALES_RULE = 'sales.allSaleRule';
//
//
//    const LOG_FILE = 'Proxy.log';
//
//    /**
//     * @param Header $header
//     * @param $request
//     * @return mixed 返回是获取到的数据
//     */
//    public static function sendRequest($header, $request)
//    {
//        list($ip, $port) = self::getRoute($header->getRoute());
//        $swClient = self::getSWClient($ip, $port, 5);
//        return self::sendMessage(Message::pack($header, $request), 5, $swClient);
//    }
//
//    /**
//     * @param string $msg
//     * @param int $retry
//     * @param \swoole_client $swClient
//     * @throws \Exception
//     * @return mixed 返回是获取到的数据
//     */
//    public static function sendMessage($msg, $retry = 5, $swClient = null)
//    {
//        $startTime = microtime(1);
//
//        if ($swClient == null) {
//            $swClient = self::getSWClient($ip, $port, $retry);
//        }
//
//        try {
//            // 没有发出去就重试
//            $success = false;
//            while (!$success && $retry-- > 0) {
//                try {
//                    $swClient->send($msg);
//                    $success = true;
//                } catch (\Exception $e) {
//                    $success = false;
//                }
//            }
//
//            $recvMsg = $swClient->recv();
//            $message = new Message();
//            $message->unpackResponse($recvMsg);
//
//            if ($message->getHeader()->getCode() == 0) {
//                $data = $message->getPackageBody();
//                ToolsAbstract::log('sendMessage:recv,status=OK,data=' . $data, self::LOG_FILE);
//                return $data;
//            } else {
//                $data = $message->getHeader()->getMsg();
//                ToolsAbstract::log('sendMessage:recv,status=FAILED,msg=' . $data, self::LOG_FILE);
//                throw new \Exception($data, $message->getHeader()->getCode());
//            }
//        } catch (\Exception $e) {
//            ToolsAbstract::log($e->__toString(), self::LOG_FILE);
//            throw $e;
//        } finally {
//            $elapsed = microtime(1) - $startTime;
//            ToolsAbstract::log('elapsed=' . $elapsed, self::LOG_FILE);
//        }
//    }
//
//    /**
//     * @param int $retry
//     * @return \swoole_client
//     * @throws \Exception
//     */
//    public static function getSWClient($ip = null, $port = null, $retry = 5)
//    {
//        $fp = null;
//        /**@var \swoole_client[] $clients */
//        static $clients = [];
//        $key = 'c_' . $ip . $port;
//
//        ToolsAbstract::log('getSWClient()', self::LOG_FILE);
//
//        if (!isset($clients[$key]) || !$clients[$key]->isConnected()) {
//            while (!$fp && $retry-- > 0) {
//                ToolsAbstract::log('getSWClient() -> new()', self::LOG_FILE);
//                if (isset($clients[$key]) && $clients[$key] instanceof \swoole_client) {
//                    ToolsAbstract::log('getSWClient() -> close()', self::LOG_FILE);
//                    $clients[$key]->close(true);
//                }
//
//                if ($ip === null) {
//                    $newIp = \Yii::$app->params['ip_port']['host'];
//                } else {
//                    $newIp = $ip;
//                }
//                if ($port === null) {
//                    $newPort = ENV_SERVER_PORT;
//                } else {
//                    $newPort = $port;
//                }
//
//                if (filter_var($newIp, FILTER_VALIDATE_IP) === false) {
//                    $clients[$key] = new \swoole_client(SWOOLE_SOCK_UNIX_STREAM, SWOOLE_SOCK_SYNC);
//                } else {
//                    $clients[$key] = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
//                }
//
//                $clientConfig = \Yii::$app->params['soa_client_config'];
//                $clients[$key]->set($clientConfig);
//                $fp = $clients[$key]->connect($newIp, $newPort, static::getTimeout());
//            }
//        }
//
//        if (empty($clients[$key])) {
//            throw new \Exception('getSWClient() error!');
//        }
//        return $clients[$key];
//    }
//
//    /**
//     * @param $route
//     * @return array|bool|mixed|null|string
//     * @throws \Exception
//     */
//    public static function getRoute($route)
//    {
//        $parts = explode('.', $route);
//        if (count($parts) != 2) {
//            throw new \Exception('invalid route!!');
//        }
//
//        $modelName = $parts[0];
//        if ($route == self::ROUTE_ROUTE_REPORT) {
//            $ipPort = \Yii::$app->params['proxy_ip_port'];
//            if (!is_array($ipPort)) {
//                throw new \Exception('ip port config not found');
//            }
//            $localHost = $ipPort['localHost'];
//            $localPort = $ipPort['localPort'];
//            return [$localHost, $localPort];
//        }
//
//        $redis = ToolsAbstract::getRouteRedis();
//        $tableName = self::KEY_INTERNAL_SERVICE;
//        if ($redis->hExists($tableName, $modelName)) {
//            $dsn = $redis->hGet($tableName, $modelName);
//            list($ip, $port) = explode(':', $dsn);
//            if (isset($ip, $port)) {
//                return [$ip, $port];
//            }
//        }
//        throw new \Exception('not match route!!');
//    }
//
//    /**
//     * @return int
//     */
//    protected static function getTimeout()
//    {
//        return 180;
//    }
}