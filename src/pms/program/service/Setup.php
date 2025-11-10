<?php

namespace pms\program\service;

use pms\app\LifecycleHookApp;
use pms\app\ServiceApp;
use pms\contract\LifecycleInterface;
use pms\program\boot\Options;

class Setup implements LifecycleInterface
{
	
	public static function start(string $rootPath, Options $bootOptions): void
	{
		/**
		 * @var ServiceApp[] $service
		 */
		$service = config('service', []);
		
		foreach ($service as $item) {
			if (!empty($item) && class_exists($item)) {
				if(!is_subclass_of($item, ServiceApp::class)){
					continue;
				}
				if (!class_exists($item::$hookClass)){
					if (!$item::$ignore) {
						throw new \Exception("service {$item}: hookClass {$item::$hookClass} is not exists");
					}
					continue;
				}
				if (is_subclass_of($item::$hookClass, LifecycleHookApp::class)) {
					/**
					 * @var LifecycleHookApp $hook
					 */
					$hook = $item::$hookClass;
					$lifecycle = $item::$lifecycle;
					$hook::mount($lifecycle, [$item, 'start']);
				} else {
					throw new \Exception("service {$item}: {$item::$hookClass} is not LifecycleHookApp");
				}
			}
		}
	}
	
}