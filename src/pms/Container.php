<?php

namespace pms;


use pms\annotate\Inject;
use pms\contract\ContainerInterface;
use pms\exception\ClassNotFoundException;
use pms\exception\InjectException;
use ReflectionClass;

abstract class Container implements ContainerInterface {
    protected array $args = [];
    protected array $bind = [];
    protected array $instances = [];

    public function getClass(string|ReflectionClass $class): ReflectionClass
    {
        if(is_string($class)){
            try{
                $class = new ReflectionClass($class);
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
        $className = $class->getName();
        $properties = $class->getProperties();
        foreach ($properties as $property){
            $attrs = $property->getAttributes();
            if(!empty($attrs)){
                foreach ($attrs as $attr){
                    switch ($attr->getName()){
                        case Inject::class:
                            $arg = $attr->getArguments();
                            if(count($arg) >= 1){
                                $name = $attr->getArguments()[0];
                                try{
                                    $inject = $this->get($name);
                                }catch (\Throwable $e){
                                    throw new InjectException("can't auto Inject(\\$name:class) to $".$property->getName()." property in $className,because it's not in the provider list.",$e);
                                }
                                $pro = $class->getProperty($property->getName());
                                $pro->setValue($instance,$inject);
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        return $instance;
    }

    public function getMethodArgs(\ReflectionClass $class,string $methodName,$args = []): array{
        if(!$class->hasMethod($methodName)){
            return [];
        }
        $method = $class->getMethod($methodName);
        $className = $class->getName();
        $parameters = $method->getParameters();
        $arg = [];
        foreach ($parameters as $value){
            if(isset($args[$value->getName()])){
                $arg[$value->getPosition()] = $args[$value->getName()];
            }else if($value->hasType()){
                $type = $value->getType();
                if(!$type->isBuiltin()){
                    $name = $type->getName();
                    if(!isset($this->instances[$name])){
                        if(isset($this->bind[$name])){
                            $this->instances[$name] = $this->invokeClass($name);
                        }else{
                            throw new InjectException('inject ('.$name.') is not exists');
                        }
                    }
                    $arg[$value->getPosition()] = $this->instances[$name];
                }else if($value->isDefaultValueAvailable()){
                    $arg[$value->getPosition()] = $value->getDefaultValue();
                }else{
                    throw new InjectException($className.' method '.$methodName.':can\'t auto inject "'.$value->getName().'" '.'in parameter '.($value->getPosition()+1).",unless you can give it's a default value");
                }
            }else if($value->isDefaultValueAvailable()){
                $arg[$value->getPosition()] = $value->getDefaultValue();
            }else{
                throw new InjectException($className.' method '.$methodName.':can\'t auto inject "'.$value->getName().'" '.'in parameter '.($value->getPosition()+1).",unless you can give it's a default value");
            }
        }
        return $arg;
    }

    public function get($name):mixed{
        if($this->has($name)){
            return $this->make($name,$this->args);
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