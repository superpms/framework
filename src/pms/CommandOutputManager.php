<?php

namespace pms;


use pms\app\inject\command\OutputInject;
use pms\server\example\command\CommandOutput;

/**
 * @see OutputInject
 * @mixin OutputInject
 */
class CommandOutputManager
{
    public static function __callStatic(string $name, array $arguments){
        return CommandOutput::$name(...$arguments);
    }

    public function __call(string $name, array $arguments)
    {
        return CommandOutput::$name(...$arguments);
    }
}