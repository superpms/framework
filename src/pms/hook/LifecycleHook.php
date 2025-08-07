<?php

namespace pms\hook;


use pms\core\contract\HookInterface;
use pms\core\contract\LifecycleInterface;

class LifecycleHook implements HookInterface{

    public static array $container = [
        \pms\core\boot\Setup::class
    ];

    public static function mount(string|\Closure $class): bool{
        if(
            (is_string($class) && class_exists($class))
            || ($class instanceof \Closure)
        ){
            self::$container[] = $class;
            return true;
        }
        return false;
    }

    public static function run(string $rootPath): void{
        foreach (self::$container as $class){
            if($class instanceof \Closure){
                $class($rootPath);
            }else{
                /**
                 * @var $class LifecycleInterface
                 */
                $class::start($rootPath);
            }

        }
    }


}