<?php

namespace pms\facade;

use pms\PluginsManager;
use pms\Facade;

/**
 * @see PluginsManager
 * @mixin PluginsManager
 */
class Plugins extends Facade{
    protected static function getFacadeClass(): string
    {
        return PluginsManager::class;
    }

}