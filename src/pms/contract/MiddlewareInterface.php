<?php

namespace pms\contract;

interface MiddlewareInterface
{
    public function handle():void;
}