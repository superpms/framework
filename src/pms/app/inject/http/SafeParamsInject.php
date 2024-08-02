<?php

namespace pms\app\inject\http;
interface SafeParamsInject{

    public function get(string $name=null, mixed $default = null):mixed;
    public function getChain(string $name=null, mixed $default = null):mixed;
    public function getPkg(string $name=null, mixed $default = null):mixed;
    public function getPackage(string $name=null, mixed $default = null):mixed;

    public function getAll(): array;
}