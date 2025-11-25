<?php

namespace pms\app;

use pms\contract\LifecycleInterface;

/**
 * 服务应用
 */
abstract class ServiceApp implements LifecycleInterface
{

	/**
	 * 生命周期钩子类
	 * @var string $hookClass
	 */
    public static string $hookClass;
	
	/**
	 * $hookClass 中对应的 生命周期
	 * @var string|array $lifecycle
	 */
	public static string|array $lifecycle;
	
	/**
	 * 当 $hookClass 不存在时是否忽略
	 * @var bool $ignore
	 */
	public static bool $ignore = false;
	
	
	/**
	 * 当 $adapter 不为false时,服务将切换为连接器应用
	 * @var bool $adapter
	 */
	public static false|string $adapter = false;
	
	
	public function __toString(): string
	{
		return static::class;
	}
	
}