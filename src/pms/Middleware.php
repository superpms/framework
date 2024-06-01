<?php

namespace pms;

use pms\contract\MiddlewareInterface;
use pms\inject\Request;
use ReflectionClass;
abstract class Middleware implements MiddlewareInterface{

    protected ReflectionClass $class;
    protected Request $request;
    public function __construct(ReflectionClass $class,Request $request){
        $this->class = $class;
        $this->request = $request;
    }
}