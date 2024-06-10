<?php

namespace pms\facade;

use pms\Facade;
use pms\server\plugin\Driver;

/**
 * @see Driver
 * @mixin Driver
 */
class PluginsConfig extends Facade{
    protected static function getFacadeClass(): string
    {
        return Driver::class;
    }

}