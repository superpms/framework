<?php

namespace pms\facade;

use pms\core\driver\path\Driver;
use pms\Facade;

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