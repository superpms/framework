<?php

namespace pms;

use ArrayAccess;
use Iterator;
use JsonSerializable;

class ArrayObjectAccess implements JsonSerializable,Iterator,ArrayAccess
{

    protected array $data = [];
    protected int $position = 0;

    public function jsonSerialize(): mixed
    {
        return $this->data;
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
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

}