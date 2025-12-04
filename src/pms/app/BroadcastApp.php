<?php

namespace pms\app;

abstract class BroadcastApp
{

    protected static array $container = [];

    /**
     * @param callable|array $closure  监听回调函数
     * @param bool|array     $immediate 是否立即触发
     * @return void
     */
    public static function listener(callable|array $closure,bool|array $immediate = false): void
    {
        if(!is_array($closure)){
            $closure = [$closure];
        }
        foreach ($closure as $value){
            static::$container[] = $value;
        }


        if($immediate !== false){
            $args = [];
            if(is_array($immediate)){
                $args = $immediate;
            }
            static::trigger(...$args);
        }
    }

    public static function trigger(...$args): void
    {
        foreach (static::$container as $value){
            call_user_func($value, ...$args);
        }
    }

    public static function audit(): array
    {
        return static::$container;
    }


}