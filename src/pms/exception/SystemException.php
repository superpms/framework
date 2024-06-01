<?php
namespace pms\exception;

use RuntimeException;

class SystemException extends RuntimeException{
    public function __construct(string $message,$previous = null){
        parent::__construct($message,0,$previous);
    }

}