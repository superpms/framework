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
            if (class_exists($item::$hookClass) && is_subclass_of($item::$hookClass, LifecycleHookApp::class)) {
                /**
                 * @var LifecycleHookApp $hook
                 */
                $hook = $item::$hookClass;
                $lifecycle = $item::$lifecycle;
                $hook::mount($lifecycle, [$item, 'start']);
            } else {
                throw new \Exception("service {$item::class}: {$item::$hookClass} is not LifecycleHookApp");
            }
        }

    }

}