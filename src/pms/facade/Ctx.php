<?php

namespace pms\facade;

use pms\core\driver\context\Driver;
use pms\Facade;

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