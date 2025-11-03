<?php

namespace pms\hook;


use pms\app\LifecycleHookApp;

class LifecycleHook extends LifecycleHookApp {

    protected static array $container = [
        LIFECYCLE_BOOT => [
            [\pms\program\boot\Setup::class, 'start'],
            [\pms\program\service\Setup::class, 'start'],
        ],
    ];

}