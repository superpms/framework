<?php

namespace pms\core\boot;

use pms\contract\LifecycleInterface;
use pms\facade\Config;
use pms\facade\Path;
use pms\hook\AutoloadHook;

class Setup implements LifecycleInterface {

    protected static Options $bootConfig;
    protected static  string $rootPath;
    public static function start(string $rootPath): void{
        static::$rootPath = $rootPath;

        /**
         * 初始系统引导文件
         */
        static::initBootConfig();

        /**
         * 项目级 PHP配置覆盖
         */
        static::initPhpIni();

        /**
         * 初始化路径导航系统
         */
        static::initPath();

        /**
         * 初始化自动导入文件
         */
        static::initAutoload();

        /**
         * 初始化系项目配置
         */
        static::initConfig();

    }


    protected static function initBootConfig(): void{
        $bootFile = path_join(static::$rootPath, 'boot.json');
        if (!file_exists($bootFile)) {
            exit("系统引导文件不存在");
        }
        $fileContent = file_get_contents($bootFile);
        if (!json_validate($fileContent)) {
            exit("系统引导文件读取错误");
        }
        $bootConfig = json_decode($fileContent);
        static::$bootConfig = new Options($bootConfig);
    }
    protected static function initPhpIni(): void{
        date_default_timezone_set(static::$bootConfig->timezone);
        if (!static::$bootConfig->error_debug) {
            ini_set('display_errors', 'Off');
        }
        if (static::$bootConfig->log_debug) {
            ini_set('log_errors', 'On');
            ini_set('error_log', Path::getRuntime('/base/error.log'));
        }
        foreach (static::$bootConfig->php_ini as $key => $item) {
            ini_set($key, $item);
        }
    }
    protected static function initPath(): void{
        $vendorPath = realpath(dirname(__DIR__));
        static::$rootPath = static::$rootPath !== "" ? static::$rootPath : rtrim(dirname($vendorPath, 4), DIRECTORY_SEPARATOR);
        Path::init([
            'root' => static::$rootPath,
            'app' => path_join(static::$rootPath, static::$bootConfig->dir_app),
            'config' => path_join(static::$rootPath, static::$bootConfig->dir_config),
            'runtime' => path_join(static::$rootPath, static::$bootConfig->dir_runtime),
        ]);
        $paths = [
            Path::getRuntime('/app'),
            Path::getRuntime('/base'),
            Path::getRuntime('/interpreter'),
            Path::getRuntime('/interpreter/pid'),
            Path::getRuntime('/interpreter/log'),
        ];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }

    protected static function initAutoload(): void{
        AutoloadHook::mount(static::$bootConfig->autoload);
        AutoloadHook::run();
    }

    protected static function initConfig(): void{
        /**
         * 加载系统配置
         */
        $configPath = Path::getConfig();
        $files = [];
        if (is_dir($configPath)) {
            $files = glob($configPath . '/*' . '.php');
        }
        if (is_dev()) {
            $devConfigPath = path_join($configPath, "dev");
            if (is_dir($devConfigPath)) {
                $files = array_merge($files, glob($devConfigPath . '/*.php'));
            }
        }
        Config::init(load_file_config($files));
    }


}