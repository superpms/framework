<?php

namespace pms\exception;

use RuntimeException;

class AuthException extends RuntimeException{
    public function __construct(string $message){
        parent::__construct($message);
    }

}