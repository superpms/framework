<?php

namespace pms\facade;

use pms\Facade;
use pms\program\boot\Driver;

/**
 * @see Driver
 * @mixin Driver
 */
class BootOptions extends Facade
{

    protected static function getFacadeClass(): string
    {
        return Driver::class;
    }


}