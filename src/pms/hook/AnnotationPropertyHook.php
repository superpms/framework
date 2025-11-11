<?php

namespace pms\hook;

use pms\Container;
use pms\contract\HookAppInterface;
use ReflectionClass;
use ReflectionProperty;

class AnnotationPropertyHook implements HookAppInterface
{
	protected static array $container = [];
	
	public static function mount(string $annotateClass, \Closure $fn): bool
	{
		if (!isset(static::$container[$annotateClass])) {
			static::$container[$annotateClass] = $fn;
			return true;
		}
		return false;
	}
	
	public static function run(ReflectionClass $class, &$obj, Container &$server): object
	{
		$properties = $class->getProperties();
		
		foreach ($properties as $property) {
			// 当前成员的注解集合
			$attrs = $property->getAttributes();
			if (!empty($attrs)) {
				foreach ($attrs as $attr) {
					// 当前注解是否挂载
					if (isset(static::$container[$attr->getName()])) {
						// 获取当前注解传参
						$attrArguments = $attr->getArguments();
						
						/**
						 * @var ReflectionClass    $class    需要实例化的类
						 * @var object             $obj      实例化后的对象
						 * @var Container          $server   当前运行容器
						 * @var ReflectionProperty $property 当前正在解析的成员
						 * @var array              $attrArguments 当前注解传参
						 */
						static::$container[$attr->getName()]($class, $obj, $server, $property, $attrArguments);
					}
				}
			}
		}
		return $obj;
	}
	
	public static function audit(): array
	{
		return static::$container;
	}
}