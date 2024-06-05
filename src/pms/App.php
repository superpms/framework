<?php

namespace pms;

use pms\facade\Config;
use pms\facade\Db;
use pms\facade\Path;
use pms\facade\RDb;
use pms\server\Command;
use pms\server\HttpSwoole;
use pms\server\HttpWeb;

/**
 * @property HttpWeb $httpWeb
 * @property HttpSwoole $httpSwoole
 * @property Command $command
 */
class App {

    public function __construct($rootPath = ""){
        $this->pathInit($rootPath);
        $this->configInit();
        $this->phpiniInit();
        $this->databaseInit();
        $userCommon = Path::getCore('/common.php');
        if(is_file($userCommon)){
            include_once $userCommon;
        }
    }

    protected function pathInit($rootPath = ""): void{
        $serverPath = realpath(dirname(__DIR__));
        $rootPath = $rootPath !== "" ? $rootPath : rtrim(dirname($serverPath, 4), DIRECTORY_SEPARATOR);
        $corePath = $rootPath . DIRECTORY_SEPARATOR.'core';
        Path::init([
            'server' => $serverPath,
            'root'=>$rootPath,
            'app' => $rootPath . DIRECTORY_SEPARATOR . 'app',
            'core' => $corePath,
            'config' => $corePath . DIRECTORY_SEPARATOR."config",
            'plugins' => $rootPath . DIRECTORY_SEPARATOR . 'plugins',
            'public' => $rootPath . DIRECTORY_SEPARATOR . 'public',
            'runtime' => $rootPath . DIRECTORY_SEPARATOR . 'runtime',
            'vendor' => $rootPath . DIRECTORY_SEPARATOR . 'vendor',
        ]);
    }

    protected function configInit(): void{
        Config::init(loadConfig(Path::getConfig()));
    }

    protected function phpiniInit(): void{
        set_error_handler('customErrorHandler');
        date_default_timezone_set(config('app.default_timezone', 'Asia/Shanghai'));
        $debug = config('app.debug',false);
        if(!$debug){
            ini_set('display_errors', 'Off');
        }
        $logDebug = config('app.log_debug',false);
        if($logDebug){
            ini_set('log_errors', 'On');
            ini_set('error_log', Path::getRuntime('/log/error.log'));
        }
        $baseInit = config('php-ini',[]);
        foreach ($baseInit as $key => $item){
            ini_set($key,$item);
        }
    }
    protected function databaseInit(): void{
        Db::setConfig(config('database'));
        RDb::setConfig(config('redis'));
    }

    protected array $server = [
        'httpWeb'=>HttpWeb::class,
        'httpSwoole'=>HttpSwoole::class,
        'command'=>Command::class
    ];

    public function __get(string $name){
        if(!isset($this->server[$name])){
            exit("服务不存在");
        }
        $this->server[$name]::run();
    }

}