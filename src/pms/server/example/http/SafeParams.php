<?php

namespace pms\server\example\http;
use pms\app\inject\http\SafeParamsInject as inf;
use pms\ArrayObjectAccess;

class SafeParams extends ArrayObjectAccess implements inf {

    public function __construct(array $params){
        $this->data = $params;
    }

    public function get(string $name = null, string $default = null):mixed{
        if($name === null){
            return $this->data;
        }
        return $this->data[$name] ?? $default;
    }

    public function getAll(): array
    {
        return $this->data;
    }

}