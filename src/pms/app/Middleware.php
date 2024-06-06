<?php

namespace pms\app;

use pms\app\inject\http\RequestInject;
use pms\contract\MiddlewareInterface;
use ReflectionClass;

abstract class Middleware implements MiddlewareInterface{

    protected ReflectionClass $class;
    protected RequestInject $request;
    final public function __construct(ReflectionClass $class, RequestInject $request){
        $this->class = $class;
        $this->request = $request;
    }

}