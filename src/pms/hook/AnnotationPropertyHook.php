<?php

namespace pms\hook;

use pms\Container;
use pms\contract\HookInterface;
use ReflectionClass;

class AnnotationPropertyHook implements HookInterface
{
    protected static array $container = [];

    public static function mount(string $annotateClass, \Closure $fn): bool
    {
        if (!isset(static::$container[$annotateClass])) {
            static::$container[$annotateClass] = $fn;
            return true;
        }
        return false;
    }

    public static function run(ReflectionClass $class, &$obj, Container &$server): object{
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $attrs = $property->getAttributes();
            if (!empty($attrs)) {
                foreach ($attrs as $attr){
                    if(isset(static::$container[$attr->getName()])){
                        static::$container[$attr->getName()]($class,$property,$attr->getArguments(),$obj,$server);
                    }
                }
            }
        }
        return $obj;
    }

    public static function audit(): array
    {
        return static::$container;
    }
}