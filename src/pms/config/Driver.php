<?php

namespace pms\config;

use pms\helper\Data;

class Driver
{
    protected array $data = [];

    public function init(array $data): void{
        $this->data = $data;
    }

    public function join(array $data): void{
        $this->data = [
            ...$this->data,
            ...$data,
        ];
    }

    public function get(string $name = null, $default = null){
        if ($name === null) {
            return $this->data;
        }
        $data = Data::getChainData($this->data,$name);
        return $data !== null ? $data : $default;
    }
}