<?php

namespace pms\helper\hrb;

use pms\ArrayObjectAccess;

abstract class HRBOptions extends ArrayObjectAccess
{
    /**
     * @throws \Exception
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? throw new \Exception(static::class.":$name 属性为必填项");
    }

    public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }
}