<?php

namespace pms\program\boot;

use pms\annotate\Inject;
use pms\Container;
use pms\contract\LifecycleInterface;
use pms\facade\Config;
use pms\facade\Path;
use pms\hook\AnnotationPropertyHook;
use pms\hook\AutoloadHook;

class Setup implements LifecycleInterface
{

    protected static string $rootPath;

    public static function start(string $rootPath, Options $bootOptions): void
    {
        static::$rootPath = $rootPath;


        /**
         * 初始化路径导航系统
         */
        static::initPath($bootOptions);

        /**
         * 项目级 PHP配置覆盖
         */
        static::initPhpIni($bootOptions);

        /**
         * PHP扩展验证
         */
        static::validatePhpExtension($bootOptions);

        /**
         * PHP函数验证
         */
        static::validatePhpFunction($bootOptions);

        /**
         * 初始化自动导入文件
         */
        static::initAutoload($bootOptions);

        /**
         * 初始化系项目配置
         */
        Config::fetchConfig(Path::getConfig());

        /**
         * 挂载依赖注入
         */
        static::initInject();

    }

    protected static function initPhpIni(Options $bootOptions): void
    {
        date_default_timezone_set($bootOptions->timezone);
        foreach ($bootOptions->php_ini as $key => $item) {
            ini_set($key, $item);
        }
        if (!$bootOptions->error_debug) {
            ini_set('display_errors', 'Off');
        }
        if ($bootOptions->log_debug) {
            ini_set('log_errors', 'On');
            ini_set('error_log', Path::getRuntime('/base/error.log'));
        }
    }

    protected static function initPath(Options $bootOptions): void
    {
        $vendorPath = realpath(dirname(__DIR__));
        static::$rootPath = static::$rootPath !== "" ? static::$rootPath : rtrim(dirname($vendorPath, 4), DIRECTORY_SEPARATOR);
        Path::init([
            'root' => static::$rootPath,
            'app' => path_join(static::$rootPath, $bootOptions->dir_app),
            'config' => path_join(static::$rootPath, $bootOptions->dir_config),
            'runtime' => path_join(static::$rootPath, $bootOptions->dir_runtime),
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

    protected static function initAutoload(Options $bootOptions): void
    {
        AutoloadHook::mount($bootOptions->autoload);
        AutoloadHook::run();
    }

    protected static function initInject(): void
    {
        AnnotationPropertyHook::mount(Inject::class, function (\ReflectionClass $class, \ReflectionProperty $property, array $attrArgs,object  &$obj,Container &$server) {
            if(count($attrArgs) >= 1){
                $name = $attrArgs[0];
                array_shift($attrArgs);
                if($server->has($name)){
                    $inject = $server->get($name,$attrArgs);
                }else{
                    $inject = $server->invokeClass($name,$attrArgs);
                    $server->put($name,$inject);
                }
                $pro = $class->getProperty($property->getName());
                $pro->setValue($obj,$inject);
            }
        });
    }

    public static function validatePhpFunction(Options $bootOptions): void
    {
        foreach ($bootOptions->php_function as $item){
            if(!str_starts_with($item, '#') && !function_exists($item)){
                throw new \Exception("PHP函数 {$item} 无法使用");
            }
        }
    }
    public static function validatePhpExtension(Options $bootOptions): void
    {
        foreach ($bootOptions->php_extension as $item){
            if(!str_starts_with($item, '#') && !extension_loaded($item)){
                throw new \Exception("PHP扩展 {$item} 尚未安装");
            }
        }
    }

}