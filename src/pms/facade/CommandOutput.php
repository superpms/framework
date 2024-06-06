<?php

namespace pms\facade;

use pms\CommandOutputManager;
use pms\Facade;

/**
 * @see CommandOutputManager
 * @mixin CommandOutputManager
 */
class CommandOutput extends Facade
{
    protected static function getFacadeClass(): string
    {
        return CommandOutputManager::class;
    }

}