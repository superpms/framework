<?php

namespace pms\facade;

use pms\Facade;
use pms\program\service\Driver;

/**
 * @see Driver
 * @mixin Driver
 */
class Service extends Facade
{
	protected static function getFacadeClass(): string
	{
		return Driver::class;
	}
	
}