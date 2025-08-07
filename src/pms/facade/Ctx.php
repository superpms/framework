<?php

namespace pms\facade;

use pms\Facade;
use pms\program\ctx\Driver;

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