<?php

namespace pms\facade;

use pms\Facade;
use pms\plugin\Driver;

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