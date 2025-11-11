<?php

namespace pms\app;


use pms\contract\HookAppInterface;

/**
 * 连接器钩子应用
 * @note 连接器和生命周期的区别是：
 *       生命周期：到达某个生命周期时，触发当前生命周期下的所有钩子
 *       连接器：到达某个生命周期时，仅触发当前连接点下的钩子,控制更加精细。
 *       连接器：同一个生命周期下的同一个连接器只能挂载一个,重复挂载会覆盖
 */
abstract class AdapterHookApp implements HookAppInterface
{
	protected static array $container = [];
	
	/**
	 * 挂载生命周期连接器
	 * @note 默认无法挂载至不存在的生命周期
	 * @param  string           $lifecycle  生命周期
	 * @param  callable|string  $callable   连接器回调方法
	 * @param  string           $adapter    连接器名称
	 * @return bool
	 */
	public static function mount(string $lifecycle, string $adapter, callable|string $callable): bool
	{
		if (!array_key_exists($lifecycle, static::$container)) {
			return false;
		}
		if (!is_array(static::$container[$lifecycle])) {
			static::$container[$lifecycle] = [];
		}
		if ($adapter !== '') {
			return false;
		}
		if (!is_callable($callable)) {
			$callable = [$callable, 'entry'];
			if (!is_callable($callable)) {
				return false;
			}
		}
		static::$container[$lifecycle][$adapter] = $callable;
		return true;
	}
	
	public static function run(string $lifecycle, string $adapter, ...$args)
	{
		if (!array_key_exists($lifecycle, static::$container)) {
			return false;
		}
		if (!array_key_exists($adapter, static::$container[$lifecycle])) {
			return false;
		}
		return call_user_func_array(static::$container[$lifecycle][$adapter], $args);
	}
	
	public static function audit()
	{
		return static::$container;
	}
	
	public function __toString(): string
	{
		return static::class;
	}
}