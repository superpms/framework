<?php

namespace pms\program\boot;

use pms\annotate\Inject;
use pms\Container;
use pms\contract\LifecycleInterface;
use pms\facade\BootOptions;
use pms\facade\Config;
use pms\facade\Path;
use pms\facade\Service;
use pms\hook\AnnotationPropertyHook;
use pms\hook\AutoloadHook;

class  Setup implements LifecycleInterface
{
	
	protected static string $rootPath;
	
	public static function entry(string $rootPath): void
	{
		
		static::$rootPath = $rootPath;
		
		/**
		 * 初始化路径导航系统
		 */
		static::initPath();
		
		/**
		 * 项目级 PHP配置覆盖
		 */
		static::initPhpIni();
		
		/**
		 * PHP扩展验证
		 */
		static::validatePhpExtension();
		
		/**
		 * PHP函数验证
		 */
		static::validatePhpFunction();
		
		/**
		 * 初始化自动导入文件
		 */
		static::initAutoload();
		
		/**
		 * 初始化系项目配置
		 */
		Config::fetchConfig(Path::getConfig());
		
		/**
		 * 挂载依赖注入
		 */
		static::initInject();
		
		/**
		 * 挂载服务属性
		 */
		static::initService();
		
	}
	
	protected static function initPhpIni(): void
	{
		date_default_timezone_set(BootOptions::get_timezone());
		foreach (BootOptions::get_php_ini() as $key => $item) {
			ini_set($key, $item);
		}
		if (!BootOptions::get_error_debug()) {
			ini_set('display_errors', 'Off');
		}
		if (BootOptions::get_log_debug()) {
			ini_set('log_errors', 'On');
			ini_set('error_log', Path::getRuntime('/base/error.log'));
		}
	}
	
	protected static function initPath(): void
	{
		Path::init([
			'root' => static::$rootPath,
			'app' => path_join(static::$rootPath, BootOptions::get_dir_app()),
			'config' => path_join(static::$rootPath, BootOptions::get_dir_config()),
			'runtime' => path_join(static::$rootPath, BootOptions::get_dir_runtime()),
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
	
	protected static function initAutoload(): void
	{
		AutoloadHook::mount(BootOptions::get_autoload());
		AutoloadHook::run();
	}
	
	protected static function initInject(): void
	{
		AnnotationPropertyHook::mount(
			Inject::class,
			function (
				\ReflectionClass    $class,
				object              &$obj,
				Container           &$server,
				\ReflectionProperty $property,
				array               $attrArgs
			) {
				if (count($attrArgs) >= 1) {
					$name = array_shift($attrArgs);
					if ($server->has($name)) {
						$inject = $server->get($name, $attrArgs);
					} else {
						$inject = $server->invokeClass($name, $attrArgs);
						$server->put($name, $inject);
					}
					$pro = $class->getProperty($property->getName());
					$pro->setValue($obj, $inject);
				}
			});
	}
	
	public static function validatePhpFunction(): void
	{
		foreach (BootOptions::get_php_function() as $item) {
			if (!str_starts_with($item, '#') && !function_exists($item)) {
				throw new \Exception("PHP函数 {$item} 无法使用");
			}
		}
	}
	
	public static function validatePhpExtension(): void{
		foreach (BootOptions::get_php_extension() as $item) {
			if (!str_starts_with($item, '#') && !extension_loaded($item)) {
				throw new \Exception("PHP扩展 {$item} 尚未安装");
			}
		}
	}
	
	public static function initService(): void{
		$cfg = config('service', []);
		Service::init(is_array($cfg) ? $cfg : [$cfg]);
	}
	
}