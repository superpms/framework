<?php

namespace pms\server\example\http;
use pms\app\inject\http\SafeParamsInject as inf;
use pms\ArrayObjectAccess;
use pms\helper\Data;

class SafeParams extends ArrayObjectAccess implements inf {

    public function __construct(array $params){
        $this->data = $params;
    }

    public function get(string $name = null, mixed $default = null):mixed{
        if($name === null){
            return $this->data;
        }
        return $this->data[$name] ?? $default;
    }

    public function getChain(string $name = null, mixed $default = null):mixed{
        if($name === null){
            return $this->data;
        }
        return Data::getChainData($this->data,$name) ?? $default;
    }

    public function getPkg(string $name = null, mixed $default = null): mixed
    {
        return $this->getChain($name,$default);
    }

    public function getPackage(string $name = null, mixed $default = null): mixed
    {
        return $this->getChain($name,$default);
    }

    public function getAll(): array
    {
        return $this->data;
    }

}