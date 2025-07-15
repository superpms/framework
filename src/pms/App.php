<?php

namespace pms;

use pms\core\Initialization;
use pms\facade\Interpreter;

/**
 *
 */
class App
{

    public function __construct($rootPath = ""){
        new Initialization($rootPath);
    }

    public function __get(string $name){
        Interpreter::run($name);
    }
}