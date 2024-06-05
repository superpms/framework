<?php

namespace pms\contract;

use ReflectionClass;

interface ContainerInterface
{
    public function getClass(string|ReflectionClass $class):ReflectionClass;
    public function invokeClass(string|ReflectionClass $class,array $args=[]):object;
    public function getMethodArgs(\ReflectionClass $class,string $methodName,array $args = []): array;

    public function get(string $name,array $args=[]):mixed;
    public function has(string $name): bool;
    public function make(string $name,array $args=[]):object;
    public function put(string $name,mixed $class): void;
}