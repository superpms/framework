<?php

namespace pms\facade;

use pms\Facade;
use pms\program\context\Driver;

/**
 * @see Driver
 * @mixin Driver
 */
class Ctx extends Facade
{
    protected static function getFacadeClass(): string
    {
        return Driver::class;
    }

}