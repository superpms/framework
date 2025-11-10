<?php

namespace pms\app;

use pms\contract\HookInterface;

/**
 * 生命周期钩子应用
 */
abstract class LifecycleHookApp implements HookInterface
{
	
	protected static array $container = [];
	
	/**
	 * 挂载生命周期钩子
	 * @note 默认无法挂载至不存在的生命周期
	 * @note 如需挂载至不存在的生命周期(动态生命周期)，需重写 mount() 或 提前将生命周期key加入容器
	 * @param  string           $lifecycle
	 * @param  callable|string  $callable
	 * @return bool
	 */
	public static function mount(string $lifecycle, callable|string $callable): bool
	{
		if (!array_key_exists($lifecycle, static::$container)) {
			return false;
		}
		if (!is_callable($callable)) {
			$callable = [$callable, 'start'];
			if (!is_callable($callable)) {
				return false;
			}
		}
		static::$container[$lifecycle][] = $callable;
		return true;
	}
	
	public static function run(string $lifecycle, ...$args): void
	{
		if (array_key_exists($lifecycle, static::$container)) {
			foreach (static::$container[$lifecycle] as $closure) {
				call_user_func_array($closure, $args);
			}
		}
	}
	
	public static function audit(): array
	{
		return static::$container;
	}
	
	
}