<?php

namespace pms\app;

use pms\app\inject\http\RequestInject;
use pms\contract\MiddlewareInterface;
use ReflectionClass;

abstract class Middleware implements MiddlewareInterface{

    protected ReflectionClass $class;
    protected RequestInject $request;
    protected string $app;
    final public function __construct(ReflectionClass $class, RequestInject $request,string $app){
        $this->class = $class;
        $this->request = $request;
        $this->app = $app;
    }

}