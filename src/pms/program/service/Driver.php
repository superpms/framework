<?php

namespace pms\program\service;

use pms\app\AdapterHookAppApp;
use pms\app\LifecycleHookApp;
use pms\app\ServiceApp;

/**
 * 服务驱动
 * @note 服务职责: 将 生命周期钩子 / 连接器钩子 进行自动挂载
 */
class Driver
{
	/**
	 * @var ServiceApp[] $service
	 */
	protected array $services = [];
	
	/**
	 * 服务注册
	 * @param  string  $serviceAppClass
	 * @param  string  $name
	 * @return bool
	 */
	public function register(string $serviceAppClass, string $name = ''): bool
	{
		if (class_exists($serviceAppClass)) {
			if ($name === '') {
				$this->services[] = $serviceAppClass;
			} else {
				$this->services[$name] = $serviceAppClass;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * 服务激活
	 * @param  string  $item
	 * @return bool
	 * @throws \Exception
	 */
	protected function active(string $item): bool{
		if (empty($item) || !class_exists($item) || !is_subclass_of($item, ServiceApp::class)) {
			return false;
		}
		if (!class_exists($item::$hookClass)) {
			if (!$item::$ignore) {
				throw new \Exception("service {$item}: hookClass {$item::$hookClass} is not exists");
			}
			return false;
		}
		
		/**
		 * @var LifecycleHookApp|AdapterHookAppApp $hook
		 */
		$hook = $item::$hookClass;
		$lifecycle = $item::$lifecycle;
		$adapter = $item::$adapter;
		if ($adapter === false) {
			if (!is_subclass_of($hook, LifecycleHookApp::class)) {
				throw new \Exception("service {$item}: {$hook} is not a LifecycleHookApp");
			}
			$hook::mount($lifecycle, [$item, 'entry']);
		} else {
			if (!is_subclass_of($hook, AdapterHookAppApp::class)) {
				throw new \Exception("service {$item}: {$hook} is not a AdapterHookApp");
			}
			$hook::mount($lifecycle, $adapter, [$item, 'entry']);
		}
		return true;
	}
	
	
	/**
	 * 通过服务名称将服务激活
	 * @param  string  $serviceName
	 * @return bool
	 * @throws \Exception
	 */
	public function activeName(string $serviceName): bool{
		if (isset($this->services[$serviceName])) {
			return $this->active($this->services[$serviceName]);
		}
		return false;
	}
	
	/**
	 * 初始化
	 * @param  array  $array
	 */
	public function init(array $array): void{
		$this->services = [
			...$array,
			...$this->services
		];
		foreach ($array as $key => $item) {
			$this->register($item, is_numeric($key) ? '' : $key);
		}
		
		foreach ($this->services as $item) {
			$this->active($item);
		}
	}

	
}