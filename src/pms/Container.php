<?php

namespace pms;

use pms\exception\ClassNotFoundException;
use pms\exception\SystemException;
use pms\hook\AnnotationClassHook;
use pms\hook\AnnotationPropertyHook;
use ReflectionClass;

abstract class Container{
    protected array $args = [];
    protected array $bind = [];
    protected array $instances = [];

    protected function getClass(string|ReflectionClass $class): ReflectionClass
    {
        if(is_string($class)){
            try{
                $class = new ReflectionClass($class);
                AnnotationClassHook::run($class,$this);
            }catch (\Throwable $e){
                throw new ClassNotFoundException($class, $e);
            }
        }
        return $class;
    }

    public function invokeClass(string|ReflectionClass $class,$args=[]):object{
        $class = $this->getClass($class);
        $constructArgs = $this->getMethodArgs($class,"__construct",$args);
        $instance = $class->newInstance(...$constructArgs);
        AnnotationPropertyHook::run($class,$instance,$this);
        return $instance;
    }

    protected function getMethodArgs(\ReflectionClass $class,string $methodName,$args = []): array{
        if(!$class->hasMethod($methodName)){
            return [];
        }
        $method = $class->getMethod($methodName);
        $className = $class->getName();
        $parameters = $method->getParameters();
        $arg = [];
        foreach ($parameters as $value){
            if(isset($args[$value->getPosition()])){
                $arg[$value->getPosition()] = $args[$value->getPosition()];
            }else if(isset($args[$value->getName()])){
                $arg[$value->getPosition()] = $args[$value->getName()];
            }else if($value->hasType()){
                $type = $value->getType();
                if(!$type->isBuiltin()){
                    $name = $type->getName();
                    if(!isset($this->instances[$name])){
                        $this->instances[$name] = $this->invokeClass($name);
                    }
                    $arg[$value->getPosition()] = $this->instances[$name];
                }else if($value->isDefaultValueAvailable()){
                    $arg[$value->getPosition()] = $value->getDefaultValue();
                }else{
                    throw new SystemException($className.' method '.$methodName.':can\'t auto inject "'.$value->getName().'" '.'in parameter '.($value->getPosition()+1).",unless you can give it's a default value",501);
                }
            }else if($value->isDefaultValueAvailable()){
                $arg[$value->getPosition()] = $value->getDefaultValue();
            }else{
                throw new SystemException($className.' method '.$methodName.':can\'t auto inject "'.$value->getName().'" '.'in parameter '.($value->getPosition()+1).",unless you can give it's a default value",501);
            }
        }
        return $arg;
    }

    public function get($name,$args=[]):mixed{
        if($this->has($name)){
            return $this->make($name,empty($args) ? $this->args : $args);
        }
        throw new ClassNotFoundException($name);
    }

    public function __get(string $name){
        return $this->get($name);
    }

    public function has($name): bool{
        return isset($this->bind[$name]) || isset($this->instances[$name]);
    }

    public function make($name,$args=[]):object{
        if(isset($this->instances[$name])){
            $class = $this->instances[$name];
            if(is_string($class)){
                $this->instances[$name] = $this->invokeClass($class,$args);
            }
            return $this->instances[$name];
        }
        if(isset($this->bind[$name])){
            $class = $this->bind[$name];
            return $this->invokeClass($class,$args);
        }
        throw new ClassNotFoundException($name);
    }

    public function put(string $name,$class): void
    {
        $this->instances[$name] = $class;
    }
}