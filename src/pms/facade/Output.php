<?php

namespace pms\facade;

use pms\app\inject\command\OutputInject;
use pms\Facade;
use pms\server\example\command\CommandOutput;

/**
 * @see OutputInject
 * @mixin OutputInject
 */
class Output extends Facade
{
    protected static function getFacadeClass(): string
    {
        return CommandOutput::class;
    }

}