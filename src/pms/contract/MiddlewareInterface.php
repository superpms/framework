<?php

namespace pms\contract;

use pms\inject\Request;

interface MiddlewareInterface
{
    public function handle():void;
}