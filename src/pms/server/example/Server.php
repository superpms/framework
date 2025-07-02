<?php

namespace pms\server\example;

use pms\Container;
use pms\facade\Config;
use pms\facade\Path;

abstract class Server extends Container
{
    protected string $app = '';

}