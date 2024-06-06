<?php

namespace pms\facade;

use pms\ConfigManager;
use pms\Facade;

/**
 * @see ConfigManager
 * @mixin ConfigManager
 */
class Config extends Facade
{
    protected static function getFacadeClass(): string
    {
        return ConfigManager::class;
    }

}