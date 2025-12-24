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
        return json_decode(json_encode($this->data, 320), true);
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

    public function isEmpty(): bool
    {
        return empty($this->data);
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
            return $this->missing($name);
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

    public function column(string $name): array
    {
        return array_column($this->data, $name);
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->data);
    }

    public function filter(callable $callback): array
    {
        return array_filter($this->data, $callback);
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->data, $callback, $initial);
    }

    public function each(callable $callback): void
    {
        foreach ($this->data as $key => $value) {
            $callback($value, $key);
        }
    }

    public function keys(): array
    {
        return array_keys($this->data);
    }

    public function has(string $name): bool
    {
        return array_key_exists($this->realKey($name), $this->data);
    }

    public function __construct(bool $handleProperty = true)
    {
        $tmp = [];
        if ($handleProperty) {
            // 处理class所有自带属性
            $tmp = get_object_vars($this);
            unset($tmp['data']);
            foreach ($tmp as $key => $value) {
                unset($this->$key);
            }
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