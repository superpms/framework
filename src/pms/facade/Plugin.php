<?php

namespace pms\facade;

use pms\core\driver\plugin\Driver;
use pms\Facade;

/**
 * @see Driver
 * @mixin Driver
 */
class Plugin extends Facade{
    protected static function getFacadeClass(): string
    {
        return Driver::class;
    }
}