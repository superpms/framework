<?php

namespace pms\hook;

use pms\Container;
use pms\contract\HookInterface;
use ReflectionClass;

class AnnotationClassHook implements HookInterface
{

    protected static array $container = [];

    public static function mount(string $annotateClass, \Closure $fn): bool
    {
        if(!isset(static::$container[$annotateClass])){
            static::$container[$annotateClass] = $fn;
            return true;
        }
        return false;
    }

    public static function run(ReflectionClass &$class,Container &$server): void
    {
        $classAttrs = $class->getAttributes();
        foreach ($classAttrs as $classAttr) {
            $classAttrArgs = $classAttr->getArguments();
            $attrName = $classAttr->getName();
            if(isset(static::$container[$attrName])){
                static::$container[$attrName]($class,$classAttrArgs,$server);
            }
        }
    }

    public static function audit(): array
    {
        return static::$container;
    }
}