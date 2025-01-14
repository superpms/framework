<?php

namespace pms\facade;

use pms\config\Driver;
use pms\Facade;

/**
 * @see Driver
 * @mixin Driver
 */
class Config extends Facade
{
    protected static function getFacadeClass(): string
    {
        return Driver::class;
    }

}