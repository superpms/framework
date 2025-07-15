<?php

namespace pms\core\driver\context;

class Driver
{

    protected \Closure $getFnc;
    protected \Closure $setFnc;
    protected \Closure $hasFnc;

    protected array $container = [];


    public function __construct(){
        if (defined('SWOOLE_VERSION') && SWOOLE_VERSION !== null) {
            $context = \Swoole\Coroutine::getContext(\Swoole\Coroutine::getCid());
            $context['global'] = [];
            $this->setFnc = function (string $key, mixed $value) use ($context) {
                $context['global'][$key] = $value;
            };
            $this->getFnc = function (string $key, mixed $default = null) use ($context) {
                return $context['global'][$key] ?? $default;
            };
            $this->hasFnc = function (string $key) use ($context) {
                return array_key_exists($key, $context['global']);
            };
        } else {
            $this->setFnc = function (string $key, mixed $value) {
                $this->container[$key] = $value;
            };
            $this->getFnc = function (string $key, mixed $default = null) {
                return $this->container[$key] ?? $default;
            };
            $this->hasFnc = function (string $key) {
                return array_key_exists($key, $this->container);
            };
        }
    }

    public function set(string $key, mixed $value)
    {
        $fnc = $this->setFnc;
        return $fnc($key, $value);
    }

    public function get(string $key, mixed $default = null)
    {
        $fnc = $this->getFnc;
        return $fnc($key, $default);
    }

    public function has(string $key)
    {
        $fnc = $this->hasFnc;
        return $fnc($key);
    }

}