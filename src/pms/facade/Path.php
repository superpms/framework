<?php

namespace pms\facade;

use pms\Facade;
use pms\program\path\Driver;

/**
 * @see Driver
 * @mixin Driver
 */
class Path extends Facade
{
    protected static function getFacadeClass(): string
    {
        return Driver::class;
    }

}