<?php

namespace pms\facade;

use pms\Facade;
use pms\interpreter\Driver;

/**
 * @see Driver
 * @mixin Driver
 */
class Interpreter extends Facade{

    protected static function getFacadeClass(): string
    {
        return Driver::class;
    }

}