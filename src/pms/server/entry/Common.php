<?php

namespace pms\server\entry;
use pms\contract\ServerEntryInterface;
use pms\inject\Response;
abstract class Common  implements ServerEntryInterface{
    protected string $name = "";
    public function __construct(string $rootPath = ""){
        define('__SERVER_PATH__', realpath(dirname(__DIR__)));
        define('__ROOT_PATH__', $rootPath !== "" ? $rootPath : rtrim(dirname(__SERVER_PATH__, 4), DIRECTORY_SEPARATOR));
        define('__CORE_PATH__', __ROOT_PATH__ . DIRECTORY_SEPARATOR.'core');
        define('__APP_PATH__', __ROOT_PATH__ . DIRECTORY_SEPARATOR.'app');
        define('__CONFIG_PATH__', __CORE_PATH__ . DIRECTORY_SEPARATOR."config");
        define('__RUNTIME_PATH__', __ROOT_PATH__ . DIRECTORY_SEPARATOR."runtime");
        define('__CONFIG__', $this->loadConfig());
        $this->init();
    }
    protected function init(): void
    {
        $userCommon = __CORE_PATH__ . '/common.php';
        if(is_file($userCommon)){
            include_once $userCommon;
        }
        set_error_handler('customErrorHandler');
        $this->constructPhpIni();
    }

    protected function loadConfig(): array{
        $ext = '.php';
        $config = [];
        $files = [];
        $configPath = __CONFIG_PATH__;
        if (is_dir(__CONFIG_PATH__)) {
            $files = glob(__CONFIG_PATH__ . '/*' . $ext);
        }
        $env = "production";
        if (file_exists(__ROOT_PATH__ . "/dev.lock")) {
            $env = "development";
        }
        $environmentPath = $configPath . DIRECTORY_SEPARATOR . $env;
        if (is_dir($environmentPath)) {
            $files = array_merge($files, glob($environmentPath . '/*.php'));
        }
        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (is_file($file)) {
                $filename = $file;
            } elseif (is_file($configPath . $file . $ext)) {
                $filename = $configPath . $file . $ext;
            }
            $config[strtolower($name)] = include $file;
        }
        return $config;
    }

    protected function constructPhpIni(): void{
        date_default_timezone_set(config('app.default_timezone', 'Asia/Shanghai'));
        $debug = config('app.debug',false);
        if(!$debug){
            ini_set('display_errors', 'Off');
        }
        $logDebug = config('app.log_debug',false);
        if($logDebug){
            ini_set('log_errors', 'On');
            ini_set('error_log', __RUNTIME_PATH__ . '/log/error.log');
        }
        $baseInit = config('php-ini',[]);
        foreach ($baseInit as $key => $item){
            ini_set($key,$item);
        }
    }

    protected function sendFile(string $path,Response $response): void
    {
        $filePath = join(DIRECTORY_SEPARATOR,[
            __ROOT_PATH__,
            'public',
            str_replace("/",DIRECTORY_SEPARATOR,$path)
        ]);
        if(is_file($filePath)){
            $response->header('Content-Type', mime_content_type($filePath));
            $response->end(file_get_contents($filePath));
        }else{
            $response->status(404);
        }
    }

    protected function runLog($host,$port){
        echo "【{$this->name}】 Running\n";
        foreach ([
                     "启动时间" => date("Y-m-d H:i:s"),
                     "绑定地址" => $host,
                     "绑定端口" => $port,
                     "本地地址" => "http://127.0.0.1:" . $port,
                 ] as $key => $value) {
            echo "  $key: $value\n";
        }
    }

}