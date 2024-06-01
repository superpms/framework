<?php

namespace pms\contract;

use ReflectionClass;

interface ContainerInterface
{
    public function getClass(string|ReflectionClass $class):ReflectionClass;
    public function invokeClass(string|ReflectionClass $class,$args=[]):object;
    public function getMethodArgs(\ReflectionClass $class,string $methodName,$args = []): array;

    public function get($name):mixed;
    public function has($name): bool;
    public function make($name,$args=[]):object;
    public function put(string $name,$class): void;
}