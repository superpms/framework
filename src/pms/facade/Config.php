<?php

namespace pms\facade;

use pms\Facade;
use pms\program\config\Driver;

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