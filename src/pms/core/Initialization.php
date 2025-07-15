<?php

namespace pms\core;

use pms\facade\Cache;
use pms\facade\Config;
use pms\facade\Db;
use pms\facade\Path;
use pms\facade\Plugin;
use pms\facade\RDb;

class Initialization
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
        /**
         * 在非swoole环境时，初始化swoole常量用于防止配置报错
         */
        $this->repairSwooleConstant();

        /**
         * 初始化路径系统
         */
        $this->pathInit($rootPath);

        /**
         * 创建基础runtime目录
         */
        $this->runtimeInit();

        /**
         * 初始化系统配置配置
         */
        $this->configInit();

        /**
         * 项目级 PHP配置 覆盖
         */
        $this->phpIniInit();

        /**
         * 初始化文件缓存系统
         */
        $this->fileCacheInit();

        /**
         * 初始化数据库系统
         */
        $this->databaseInit();
        $userCommon = Path::getCore('/common.php');
        if (is_file($userCommon)) {
            include_once $userCommon;
        }

        /**
         * 初始化插件系统
         */
        $this->pluginsInit();

    }


    protected function pluginsInit(): void
    {
        $pluginsRoot = Path::getPlugins();
        Plugin::init($pluginsRoot);
    }

    protected function runtimeInit(): void
    {
        $paths = [
            '/cache',
            '/pid',
            '/pid/interpreter',
            '/log',
            '/log/interpreter',
        ];
        foreach ($paths as $path) {
            if (!is_dir(Path::getRuntime($path))) {
                mkdir(Path::getRuntime($path), 0777, true);
            }
        }
    }

    protected function pathInit($rootPath = ""): void
    {
        $serverPath = realpath(dirname(__DIR__));
        $rootPath = $rootPath !== "" ? $rootPath : rtrim(dirname($serverPath, 4), DIRECTORY_SEPARATOR);
        $corePath = path_join($rootPath, 'core');
        Path::init([
            'root' => $rootPath,
            'server' => $serverPath,
            'core' => $corePath,
            'config' => path_join($corePath, "config"),
            'app' => path_join($rootPath, 'app'),
            'plugins' => path_join($rootPath, 'plugins'),
            'public' => path_join($rootPath, 'public'),
            'runtime' => path_join($rootPath, 'runtime'),
            'vendor' => path_join($rootPath, 'vendor'),
        ]);

    }


    protected function repairSwooleConstant(): void
    {
        if (defined('SWOOLE_VERSION') && SWOOLE_VERSION !== null) {
            return;
        }
        foreach ($this->swooleConstant as $item) {
            define($item, null);
        }

    }

    protected function configInit(): void
    {
        /**
         * 加载系统配置
         */
        Config::init(load_file_config(Path::getConfig()));
    }

    protected function fileCacheInit(): void
    {
        /**
         * 加载缓存配置
         */
        Cache::init(Path::getRuntime('cache'));
    }

    protected function phpIniInit(): void
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
        $baseInit = config('app.phpInI', []);
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