<?php

namespace pms\hook;

use pms\app\InterpreterApp;
use pms\contract\HookAppInterface;

class InterpreterHook implements HookAppInterface{


    protected static array $container = [];

    public static function mount(string $interpreterName, string $interpreterClass): bool{
        if(!isset(static::$container[$interpreterName])){
            static::$container[$interpreterName] = $interpreterClass;
            return true;
        }
        return false;
    }

    public static function run(string $interpreterName): mixed{
        if (!isset(static::$container[$interpreterName])) {
            exit("解释器 [{$interpreterName}] 未安装");
        }
        /**
         * @var InterpreterApp $server;
         */
        $server = static::$container[$interpreterName];
        return $server::entry();
    }

    public static function audit(): array{
        return static::$container;
    }

}