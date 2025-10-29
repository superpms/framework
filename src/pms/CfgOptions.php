<?php

namespace pms;

abstract class CfgOptions extends ArrayObjectAccess{

    protected function realName(string $name): string
    {
        return strtoupper($name);
    }
    protected function missing($name){
        throw new \Exception(static::class.":$name 属性为必填项");
    }

    public function set(string $name, mixed $value): static{
        $this->data[$this->realName($name)] = $value;
        return $this;
    }

    public function get(string $name, mixed $default=null): mixed{
        return $this->data[$this->realName($name)] ?? $default;
    }

    public function __isset(string $name)
    {
        return isset($this->data[$this->realName($name)]);
    }

    public function __get(string $name)
    {
        return $this->data[$this->realName($name)] ?? $this->missing($name);
    }

    public function __set(string $name, $value)
    {
        $this->data[$this->realName($name)] = $value;
    }

    public static function getExample(): static
    {
        return new static();
    }
    public function __call(string $name, array $arguments){
        if (str_starts_with($name, 'get') && strlen($name) > 3) {
            $name = substr($name, 3);
            return $this->get($name,...$arguments);
        }else if (str_starts_with($name, 'set') && strlen($name) > 3) {
            $name = substr($name, 3);
            return $this->set($name,...$arguments);
        }else{
            throw new \Exception("方法$name 不存在");
        }
    }
}