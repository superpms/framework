<?php

namespace pms\exception;
use RuntimeException;

class RequestParamsException extends RuntimeException
{

    public function __construct(string $message, protected string $type, protected string $field, protected string $desc,protected string $val='')
    {
        parent::__construct($message);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getDesc(): string
    {
        return $this->desc;
    }

    public function getType(): string
    {
        return $this->type;
    }
    public function getVal(): string
    {
        return $this->val;
    }


}