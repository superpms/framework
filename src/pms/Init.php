<?php

namespace pms;

use pms\facade\Cache;
use pms\facade\Config;
use pms\facade\Db;
use pms\facade\Path;
use pms\facade\RDb;

class Init
{
    protected array $swooleConstant = [
        'SWOOLE_VERSION',
        'SWOOLE_BASE',
        'SWOOLE_PROCESS',
        'SWOOLE_TCP',
        'SWOOLE_SOCK_TCP',
        'SWOOLE_TCP6',
        'SWOOLE_SOCK_TCP6',
        'SWOOLE_UDP',
        'SWOOLE_SOCK_UDP',
        'SWOOLE_UDP6',
        'SWOOLE_SOCK_UDP6',
        'SWOOLE_UNIX_DGRAM',
        'SWOOLE_UNIX_STREAM',
        'SWOOLE_SOCK_UNIX_STREAM',
        'SWOOLE_SOCK_SYNC',
        "SWOOLE_SSLv3_METHOD",
        "SWOOLE_SSLv3_SERVER_METHOD",
        "SWOOLE_SSLv3_CLIENT_METHOD",
        "SWOOLE_SSLv23_METHOD",
        "SWOOLE_SSLv23_SERVER_METHOD",
        "SWOOLE_SSLv23_CLIENT_METHOD",
        "SWOOLE_TLSv1_METHOD",
        "SWOOLE_TLSv1_SERVER_METHOD",
        "SWOOLE_TLSv1_CLIENT_METHOD",
        "SWOOLE_TLSv1_1_METHOD",
        "SWOOLE_TLSv1_1_SERVER_METHOD",
        "SWOOLE_TLSv1_1_CLIENT_METHOD",
        "SWOOLE_TLSv1_2_METHOD",
        "SWOOLE_TLSv1_2_SERVER_METHOD",
        "SWOOLE_TLSv1_2_CLIENT_METHOD",
        "SWOOLE_DTLSv1_METHOD",
        "SWOOLE_DTLSv1_SERVER_METHOD",
        "SWOOLE_DTLSv1_CLIENT_METHOD",
        "SWOOLE_DTLS_SERVER_METHOD",
        "SWOOLE_DTLS_CLIENT_METHOD",
        "SWOOLE_SSL_TLSv1",
        "SWOOLE_SSL_TLSv1_1",
        "SWOOLE_SSL_TLSv1_2",
        "SWOOLE_SSL_TLSv1_3",
        "SWOOLE_SSL_SSLv2",
        "SWOOLE_SSL_SSLv3",
        "SWOOLE_LOG_DEBUG",
        "SWOOLE_LOG_TRACE",
        "SWOOLE_LOG_INFO",
        "SWOOLE_LOG_NOTICE",
        "SWOOLE_LOG_WARNING",
        "SWOOLE_LOG_ERROR",
        "SWOOLE_LOG_NONE",
        "SWOOLE_TRACE_SERVER",
        "SWOOLE_TRACE_CLIENT",
        "SWOOLE_TRACE_BUFFER",
        "SWOOLE_TRACE_CONN",
        "SWOOLE_TRACE_EVENT",
        "SWOOLE_TRACE_WORKER",
        "SWOOLE_TRACE_REACTOR",
        "SWOOLE_TRACE_PHP",
        "SWOOLE_TRACE_HTTP2",
        "SWOOLE_TRACE_EOF_PROTOCOL",
        "SWOOLE_TRACE_LENGTH_PROTOCOL",
        "SWOOLE_TRACE_CLOSE",
        "SWOOLE_TRACE_HTTP_CLIENT",
        "SWOOLE_TRACE_COROUTINE",
        "SWOOLE_TRACE_REDIS_CLIENT",
        "SWOOLE_TRACE_MYSQL_CLIENT",
        "SWOOLE_TRACE_AIO",
        "SWOOLE_TRACE_ALL",
    ];

    public function __construct($rootPath = "")
    {
        $this->pathInit($rootPath);
        $this->configInit();
        $this->phpiniInit();
        $this->databaseInit();
        $userCommon = Path::getCore('/common.php');
        if (is_file($userCommon)) {
            include_once $userCommon;
        }
        /**
         * 加载插件全局方法
         */
        foreach (config('--plugins', []) as $item) {
            $path = Path::getPlugins($item . "/common.php");
            if (is_file($path)) {
                include_once $path;
            }
        }
        /**
         * 创建基础runtime目录
         */
        $this->runtimeInit();
    }


    protected function runtimeInit()
    {
        $paths = [
            '/cache',
            '/pid',
            '/pid/server',
            '/log',
            '/log/server',
        ];
        foreach ($paths as $path){
            if (!is_dir(Path::getRuntime($path))) {
                mkdir(Path::getRuntime($path), 0777, true);
            }
        }
    }

    protected function pathInit($rootPath = ""): void
    {
        $serverPath = realpath(dirname(__DIR__));
        $rootPath = $rootPath !== "" ? $rootPath : rtrim(dirname($serverPath, 4), DIRECTORY_SEPARATOR);
        $corePath = $rootPath . DIRECTORY_SEPARATOR . 'core';
        Path::init([
            'server' => $serverPath,
            'root' => $rootPath,
            'app' => $rootPath . DIRECTORY_SEPARATOR . 'app',
            'core' => $corePath,
            'config' => $corePath . DIRECTORY_SEPARATOR . "config",
            'plugins' => $rootPath . DIRECTORY_SEPARATOR . 'plugins',
            'public' => $rootPath . DIRECTORY_SEPARATOR . 'public',
            'runtime' => $rootPath . DIRECTORY_SEPARATOR . 'runtime',
            'vendor' => $rootPath . DIRECTORY_SEPARATOR . 'vendor',
        ]);
    }


    protected function repairSwooleConstant()
    {
        if (defined('SWOOLE_VERSION')) {
            return;
        }
        foreach ($this->swooleConstant as $item){
            define($item, 0);
        }

    }

    protected function configInit(): void{
        /**
         * 在非swoole环境时，初始化swoole常量用于防止配置报错
         */
        $this->repairSwooleConstant();

        /**
         * 加载系统配置
         */
        Config::init(loadConfig(Path::getConfig()));

        /**
         * 加载插件注册配置
         */
        $plugins = Path::getPlugins("/plugins.php");
        if (is_file($plugins)) {
            $config = include $plugins;
            Config::join([
                "--plugins" => $config
            ]);
        }

        /**
         * 加载缓存配置
         */
        Cache::init(Path::getRuntime('cache'));
    }

    protected function phpiniInit(): void
    {
        date_default_timezone_set(config('app.default_timezone', 'Asia/Shanghai'));
        $debug = config('app.debug', false);
        if (!$debug) {
            ini_set('display_errors', 'Off');
        }
        $logDebug = config('app.log_debug', false);
        if ($logDebug) {
            ini_set('log_errors', 'On');
            ini_set('error_log', Path::getRuntime('/log/error.log'));
        }
        $baseInit = config('php-ini', []);
        foreach ($baseInit as $key => $item) {
            ini_set($key, $item);
        }
    }

    protected function databaseInit(): void
    {
        $dbConfig = config('database');
        if ($dbConfig !== null) {
            Db::setConfig($dbConfig);
        }
        $rdbConfig = config('redis');
        if ($rdbConfig !== null) {
            RDb::setConfig($rdbConfig);
        }
    }
}