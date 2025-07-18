<?php

namespace pms;
use pms\hook\InterpreterHook;
use pms\hook\LifecycleHook;

class Boot
{


    public function __construct(protected string $rootPath = ""){
        LifecycleHook::run($this->rootPath);
    }

    public function __get(string $name){
        InterpreterHook::run($name);
    }
}