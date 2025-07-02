<?php

namespace pms\server;

use pms\App;
use pms\app\handler\SwooleTcpHandler;
use pms\app\handler\SwooleUdpHandler;
use pms\contract\ServerInterface;
use pms\facade\Db;
use pms\facade\Path;
use pms\facade\RDb;
use pms\Init;
use pms\server\example\command\CommandOutput;


class NewsletterSwoole implements ServerInterface
{
    protected string $name = 'newsletter-swoole server';

    public static function run()
    {
        $serverName = 'newsletter';
        $output = new CommandOutput();
        $argv = $_SERVER['argv'];
        if (count($argv) < 2) {
            self::errorEnd($output, "● 未指定任何tcp项目,服务未启动。");
        }
        $project = $argv[1];
        $projectConfig = config("server.$serverName.$project");
        if ($projectConfig === null) {
            self::errorEnd($output, "● tcp项目不存在,服务未启动。");
        }
        if (
            !array_key_exists('manager', $projectConfig)
            || !array_key_exists('services', $projectConfig)
        ) {
            self::errorEnd($output, "● tcp项目【{$project}】配置错误,服务未启动。");
        }
        $managerConfig = $projectConfig['manager'];
        $servicesConfig = $projectConfig['services'];
        if (
            !array_key_exists('host', $managerConfig)
            || !array_key_exists('port', $managerConfig)
        ) {
            self::errorEnd($output, "● tcp项目【{$project}】管理端配置不完整,服务未启动。");
        }

        if (!is_array($servicesConfig) || count($servicesConfig) === 0) {
            self::errorEnd($output, "● tcp项目【{$project}】未配置任何服务,服务已停止。");
        }
        $managerSetConfig = $managerConfig['config'] ?? [];
        if (!is_array($managerSetConfig)) {
            self::errorEnd($output, "● tcp项目【{$project}】管理端配置不正确,服务已停止。");
        }

        $managerEvent = $managerConfig['event'] ?? [];
        if (!is_array($managerEvent)) {
            self::errorEnd($output, "● tcp项目【{$project}】管理端配置的event 类型不正确,服务已停止。");
        }

        Db::isPool(true);
        RDb::isPool(true);
        $managerServer = new \Swoole\WebSocket\Server($managerConfig['host'], $managerConfig['port']);
        self::customShutDownHandler($managerServer);
        $managerServer->set([
            'log_file' => Path::getRuntime("/log/server/swoole.$serverName.$project.manager.log"),
            ...$managerSetConfig,
            'reload_async' => true,
        ]);

        $serviceMessage = [];

        foreach ($servicesConfig as $key => $service) {
            if (
                !array_key_exists('host', $service)
                || !array_key_exists('port', $service)
                || !array_key_exists('handler', $service)
                || (!is_string($service['handler']))
                || !class_exists($service['handler'])
            ) {
                self::errorEnd($output, "● tcp项目【{$project}】的 [{$key}] 服务配置不完整,服务已停止。");
            }

            $host = $service['host'];
            $port = $service['port'];
            $type = $service['type'] ?? SWOOLE_TCP;
            $handler = $service['handler'];
            $serviceSetConfig = $service['config'] ?? [];

            if (!is_array($serviceSetConfig)) {
                self::errorEnd($output, "● tcp项目【{$project}】的 [{$key}] 服务配置不正确,服务已停止。");
            }

            $prot = $managerServer->addlistener($host, $port, $type);
            if (!$prot) {
                self::errorEnd($output, "● tcp项目【{$project}】的 [{$key}] 服务与其他服务端口冲突,服务已停止。");
            }
            $prot->set([
                ...$serviceSetConfig,
                'reload_async' => true,
            ]);
            $protocol = "TCP";
            switch ($type) {
                case SWOOLE_SOCK_TCP:
                case SWOOLE_TCP:
                case SWOOLE_SOCK_TCP6:
                case SWOOLE_TCP6:
                    /**
                     * @var SwooleTcpHandler $handlerExample
                     */
                    self::serverOnEvents($prot,['connect', 'receive', 'close'],$handler,$project,$output);
                    break;
                case SWOOLE_SOCK_UDP:
                case SWOOLE_UDP:
                case SWOOLE_SOCK_UDP6:
                case SWOOLE_UDP6:
                    /**
                     * @var SwooleUdpHandler $handlerExample
                     */
                    self::serverOnEvents($prot,['packet', 'receive'],$handler,$project,$output);
                    $protocol = "UDP";
                    break;
            }

            $serviceMessage[] = [
                $output->setBoldStr("● PHP Swoole-$serverName 服务"),
                '项目名称: ' . $project,
                '基础协议: ' . $protocol,
                '服务名称: ' . $key,
                '服务IP: ' . $host,
                '服务端口: ' . $port,
                '服务处理器: ' . $handler,
                sprintf('本机访问地址: <127.0.0.1:%s/>', $port),
            ];
        }

        $managerServer->on('start', function ($server,...$args) use ($output, $managerEvent, $managerConfig, $project, $serviceMessage,$serverName) {
            $output->writeArrayBlock([
                $output->setBoldStr("● PHP Swoole-$serverName 管理端"),
                '项目名称: ' . $project,
                '服务IP: ' . $managerConfig['host'],
                '服务端口: ' . $managerConfig['port'],
                '服务处理器: ' . $managerConfig['handler'] ?? '[未设置]',
                '服务处理事件: ' . (empty($managerEvent) ? '[未设置]' : implode(',', $managerEvent)),
                sprintf('本机访问地址: <127.0.0.1:%s/>', $managerConfig['port']),
            ]);
            foreach ($serviceMessage as $value) {
                $output->writeArrayBlock($value);
            }
            self::callEvent($server,'start',$managerConfig['handler'],$project,$output,$args);
        });
        $managerServer->on('message', function ($server, ...$args) use ($output, $project,$managerConfig) {
            self::callEvent($server,'message',$managerConfig['handler'],$project,$output,$args);
        });
        $managerServer->on('workerStart', function ($server, ...$args) use ($output, $project, $managerConfig) {
            new Init(Path::getRoot());
            self::initManagerHandler($server, $managerConfig['handler'], $project);
            self::callEvent($server,'workerStart',$managerConfig['handler'],$project,$output,$args);
        });

        $managerEvent = array_filter($managerEvent, function($value) {
            // 排除 start,message,workerStart,afterReload
            // 且强制跳过 afterReload （当客制化afterReload方法后将导致reload无法重载handler）
            return !in_array(strtoupper($value), ['START', 'MESSAGE','WORKERSTART','AFTERRELOAD']);
        });
        self::serverOnEvents($managerServer,$managerEvent,$managerConfig['handler'],$project,$output);
        $managerServer->start();
    }

    public static function callEvent($server, $event, $handlerClass, $project, $output,$args)
    {
        try {
            if (!isset($server->{$handlerClass})) {
                self::initManagerHandler($server, $handlerClass, $project);
            }
            if ($server->{$handlerClass} !== false && self::isPublicMethod($server->{$handlerClass}, $event)) {
                $server->{$handlerClass}->{$event}($server, ...$args);
            }
        } catch (\Throwable $e) {
            self::jsonError($output, $project, $e, $event);
        }
        self::connectDestruct();
    }

    public static function serverOnEvents($prot, $events, $handlerClass, $project, $output)
    {
        foreach ($events as $event) {
            $prot->on($event, function ($server, ...$args) use ($handlerClass, $event, $project, $output) {
                self::callEvent($server, $event, $handlerClass, $project, $output,$args);
            });
        }
    }


    public static function initManagerHandler($server, $handler, $project)
    {
        if (!empty($handler) && class_exists($handler)) {
            $server->{$handler} = new $handler($project, 'newsletter');
        }else{
            $server->{$handler} = false;
        }
    }


    public static function errorEnd($output, $e): void
    {
        $output->end($e);
        die;
    }

    public static function jsonError($output, $project, \Throwable $e, $method): void
    {
        $output->printJsonArray([
            'project' => $project,
            'method' => $method,
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'time' => date('Y-m-d H:i:s', time())
        ]);
    }

    public static function connectDestruct(): void
    {
        try {
            $connector = Db::getInstance();
            if (!empty($connector)) {
                foreach ($connector as $value) {
                    $value->close();
                }
            }
            $connector = RDb::getInstance();
            if (!empty($connector)) {
                foreach ($connector as $value) {
                    $value->close();
                }
            }
        } catch (\Throwable $e) {
        }
    }


    public static function methodExistsCaseInsensitive($object, $method): bool
    {
        $methods = array_map('strtolower', get_class_methods($object));
        return in_array(strtolower($method), $methods);
    }

    public static function isPublicMethod($object, $method): bool
    {
        return self::methodExistsCaseInsensitive($object, $method)
            && is_callable([$object, $method]);
    }

    public static function customShutDownHandler($managerServer): void{
        register_shutdown_function(function ()use($managerServer){
            $error = error_get_last();
            if (!empty($error)) {
                swoole_clear_error();
                $managerServer->shutdown();
                exit;
            }
        });
    }

}