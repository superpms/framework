<?php

namespace pms\app;

use pms\contract\HookInterface;

abstract class LifecycleHookApp implements HookInterface
{

    protected static array $container = [];

    public static function mount(string $lifecycle, callable|string $callable): bool
    {
        if (!array_key_exists($lifecycle, static::$container)) {
           return false;
        }
        if(!is_callable($callable)){
            $callable = [$callable, 'start'];
            if(!is_callable($callable)){
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

    public static function audit(): array{
        return static::$container;
    }


}