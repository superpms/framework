<?php
namespace pms\exception;

use RuntimeException;
use Throwable;

class SystemException extends RuntimeException{

    public function __construct(string $message,$code=500,?Throwable $previous = null){
        parent::__construct($message,$code,$previous);
    }

}