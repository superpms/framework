<?php

namespace pms\facade;

use pms\Facade;
use pms\PathManager;

/**
 * @see PathManager
 * @mixin PathManager
 */
class Path extends Facade
{
    protected static function getFacadeClass(): string
    {
        return PathManager::class;
    }

}