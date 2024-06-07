<?php
namespace pms\exception;

use RuntimeException;

class SystemException extends RuntimeException{

    public function __construct(string $message,$code=500,$previous = null){
        parent::__construct($message,$code,$previous);
    }

}