<?php

namespace pms\program\config;

class Driver
{
    protected array $data = [];

    public function init(array $data): void
    {

        $this->data = [
            ...$this->data,
            ...$data,
        ];
    }

    public function mount(string $key, mixed $data): bool
    {
        $this->data[$key] = $data;
        return true;
    }

    public function set(string $key, string $name, mixed $data): bool{
        if (!isset($this->data[$key])) {
            $this->data[$key] = [];
        }
        $this->data[$key][$name] = $data;
        return true;
    }

    public function get(string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->data;
        }
        $data = array_chain($this->data, $name);
        return $data !== null ? $data : $default;
    }
}