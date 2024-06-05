<?php

namespace pms\server\example\http;
use pms\app\inject\http\SafeParamsInject as inf;
use pms\ArrayObjectAccess;

class SafeParams extends ArrayObjectAccess implements inf {
    public function __construct(array $params){
        $this->data = $params;
    }

    public function get(string $name, string $default = null):mixed{
        return $this->data[$name] ?? $default;
    }

    public function getAll(): array
    {
        return $this->data;
    }

}