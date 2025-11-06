<?php

namespace pms;

use ArrayAccess;
use Iterator;
use JsonSerializable;
use Countable;

abstract class OptionsAccess implements JsonSerializable, Iterator, ArrayAccess, Countable
{

    protected array $data = [];

    protected const upper = false;
    protected const missing = false;

    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function current(): mixed
    {
        return current($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    public function key(): mixed
    {
        return key($this->data);
    }

    public function valid(): bool
    {
        return !is_null(key($this->data));
    }

    public function rewind(): void
    {
        reset($this->data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->isset($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->set($offset, $value);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->unset($offset);
    }

    public function count(): int
    {
        return count($this->data);
    }


    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __unset(string $name): void
    {
        $this->unset($name);
    }

    public function __isset(string $name): bool
    {
        return $this->isset($name);
    }

    protected function realKey(string $key): string
    {
        if (!static::upper) {
            return $key;
        }
        return strtoupper($key);
    }

    protected function get(string $name, mixed $default = null)
    {
        if ($this->isset($name)) {
            return $this->data[$this->realKey($name)];
        }
        if (static::missing && $default === null) {
            return $this->$default($name);
        }
        return $default;
    }

    protected function set(string $name, mixed $value): static
    {
        $this->data[$this->realKey($name)] = $value;
        return $this;
    }

    protected function unset(string $name): void
    {
        unset($this->data[$this->realKey($name)]);
    }

    protected function isset(string $name): bool
    {
        return isset($this->data[$this->realKey($name)]);
    }

    protected function missing($name)
    {
        throw new \Exception(static::class . ":$name 属性为必填项");
    }

    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'get_') && strlen($name) > 4) {
            $name = substr($name, 4);
            return $this->get($name, ...$arguments);
        } else if (str_starts_with($name, 'set_') && strlen($name) > 4) {
            $name = substr($name, 4);
            return $this->set($name, ...$arguments);
        } else {
            throw new \Exception("方法$name 不存在");
        }
    }


    public function __construct(bool $initProperty = true)
    {
        $tmp = [];
        if ($initProperty) {
            $tmp = get_object_vars($this);
            unset($tmp['data']);
        }
        $real = [
            ...$this->data,
            ...$tmp,
        ];
        $this->data = [];
        foreach ($real as $key => $value) {
            if (is_object($value)) {
                $this->set($key, $value);
            }
        }
    }
}