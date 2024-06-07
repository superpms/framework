<?php

namespace pms\app\inject\http;
interface SafeParamsInject{

    public function get(string $name=null, string $default = null):mixed;

    public function getAll(): array;
}