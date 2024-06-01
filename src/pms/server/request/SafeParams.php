<?php

namespace pms\server\request;
use pms\ArrayObjectAccess;
use pms\inject\SafeParams as inf;

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