<?php

namespace pms\exception;
use RuntimeException;

class CliModeForcedInterruptException extends RuntimeException
{

    public function __construct(string $message)
    {
        parent::__construct($message);
    }



}