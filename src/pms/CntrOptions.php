<?php

namespace pms;

class CntrOptions extends ArrayObjectAccess
{
    /**
     * @throws \Exception
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? throw new \Exception("no such option: $name");
    }

    public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function __construct(){
        $tmp = get_object_vars($this);
        unset($tmp['data']);
        $this->data = [
            ...$this->data,
            ...$tmp
        ];
    }
}