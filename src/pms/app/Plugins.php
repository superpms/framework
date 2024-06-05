<?php

namespace pms\app;

abstract class Plugins{
    protected static array $instance = [];

    public function __construct(){}

    public static function instance(){
        $name = get_called_class();
        if(!isset(self::$instance[$name])){
            self::$instance[$name] = new $name();
        }
        return self::$instance[$name];
    }

    public static function ins()
    {
        return self::instance();
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return self::ins()->$name(...$arguments);
    }
}