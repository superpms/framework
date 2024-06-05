<?php
declare(strict_types=1);
namespace pms;


class Facade
{
    /**
     * 始终创建新的对象实例
     * @var bool
     */
    protected static bool $alwaysNewInstance = false;

    protected static array $instance = [];

    /**
     * 创建Facade实例
     * @static
     * @access protected
     * @param  string $class       类名或标识
     * @param  array  $args        变量
     * @param  bool   $newInstance 是否每次创建新的实例
     * @return object
     */
    protected static function createFacade(string $class = '', array $args = [], bool $newInstance = false):object
    {
        $class = $class ?: static::class;
        $facadeClass = static::getFacadeClass();
        if ($facadeClass) {
            $class = $facadeClass;
        }
        if (static::$alwaysNewInstance) {
            $newInstance = true;
        }
        if($newInstance){
            return new $class(...$args);
        }
        if(!isset(self::$instance[$class])){
            self::$instance[$class] = new $class(...$args);
        }
        return self::$instance[$class];
    }

    /**
     * 获取当前Facade对应类名
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
    }

    /**
     * 带参数实例化当前Facade类
     * @access public
     * @return object
     */
    public static function instance(...$args)
    {
        if (__CLASS__ != static::class) {
            return self::createFacade('', $args);
        }
    }


    // 调用实际类的方法
    public static function __callStatic($method, $params)
    {
        return call_user_func_array([static::createFacade(), $method], $params);
    }
}

