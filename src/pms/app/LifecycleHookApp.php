<?php

namespace pms\app;

use pms\contract\HookAppInterface;

/**
 * 生命周期钩子应用
 * @note 连接器和生命周期的区别是：
 *        生命周期：到达某个生命周期时，触发当前生命周期下的所有钩子
 *        连接器：到达某个生命周期时，仅触发当前连接点下的钩子,控制更加精细。
 *        连接器：同一个生命周期下的同一个连接器只能挂载一个,重复挂载会覆盖
 */
abstract class LifecycleHookApp implements HookAppInterface
{
	
	protected static array $container = [];
	
	/**
	 * 挂载生命周期钩子
	 * @note 默认无法挂载至不存在的生命周期
	 * @note 如需挂载至不存在的生命周期(动态生命周期)，需重写 mount() 或 提前将生命周期key加入容器
	 * @param  string|array     $lifecycles
	 * @param  callable|string  $callable
	 * @return bool
	 */
	public static function mount(string|array $lifecycles, callable|string $callable): bool
	{
		if (is_string($lifecycles)) {
			$lifecycles = [$lifecycles];
		}
		foreach ($lifecycles as $lifecycle){
			if (!array_key_exists($lifecycle, static::$container)) {
				return false;
			}
			if (!is_callable($callable)) {
				$callable = [$callable, 'entry'];
				if (!is_callable($callable)) {
					return false;
				}
			}
			static::$container[$lifecycle][] = $callable;
		}
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
	
	public function __toString(): string
	{
		return static::class;
	}
}