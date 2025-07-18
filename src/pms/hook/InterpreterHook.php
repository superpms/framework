<?php

namespace pms\hook;

use pms\app\InterpreterApp;
use pms\contract\HookInterface;

class InterpreterHook  implements HookInterface{
    public static array $container = [];
    public static function mount(string $serverName, string $serverClass): bool{
        if(!isset(static::$container[$serverName])){
            static::$container[$serverName] = $serverClass;
            return true;
        }
        return false;
    }

    public static function run(string $serverName): mixed{
        if (!isset(static::$container[$serverName])) {
            exit("解释器 [{$serverName}] 未安装");
        }
        /**
         * @var InterpreterApp $server;
         */
        $server = static::$container[$serverName];
        return $server::run();
    }

}