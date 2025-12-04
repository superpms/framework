<?php

namespace pms\program\ctx;

class Driver
{
    
    protected \Closure $getFnc;
    protected \Closure $setFnc;
    protected \Closure $hasFnc;
    
    protected array $container = [];

    public function __construct(){
        if (in_swoole()) {
            $this->setFnc = function (string $key, mixed $value){
                $cid = \Swoole\Coroutine::getCid();
                if($cid === -1){
                    $this->container[$key] = $value;
                }else{
                    $context = \Swoole\Coroutine::getContext($cid);
                    if(!isset($context['global'])){
                        $context['global'] = [];
                    }
                    $context['global'][$key] = $value;
                }
                return $this;
            };
            $this->getFnc = function (?string $key='', mixed $default = null) {
                $data = $this->getRealContainer();
                if($key === ''){
                    return $data ?? $default;
                }
                return $data[$key] ?? $default;
            };
            $this->hasFnc = function (string $key){
                $data = $this->getRealContainer();
                return array_key_exists($key, $data);
            };
        } else {
            $this->setFnc = function (string $key, mixed $value) {
                $this->container[$key] = $value;
                return $this;
            };
            $this->getFnc = function (?string $key='', mixed $default = null) {
                if($key === ''){
                    return empty($this->container) ? $default : $this->container;
                }
                return $this->container[$key] ?? $default;
            };
            $this->hasFnc = function (string $key) {
                return array_key_exists($key, $this->container);
            };
        }
    }
    
    public function set(string $key, mixed $value): static {
        $key = strtoupper($key);
        $fnc = $this->setFnc;
        return $fnc($key, $value);
    }
    
    public function get(string $key='', mixed $default = null)
    {
        $key = strtoupper($key);
        $fnc = $this->getFnc;
        return $fnc($key, $default);
    }
    
    public function has(string $key)
    {
        $key = strtoupper($key);
        $fnc = $this->hasFnc;
        return $fnc($key);
    }
    
    public function __call(string $name, array $arguments) {
        if (str_starts_with($name, 'get') && strlen($name) > 3) {
            $name = substr($name, 3);
            return $this->get($name, $arguments[0] ?? null);
        } else if (str_starts_with($name, 'set') && strlen($name) > 3) {
            $name = substr($name, 3);
            return $this->set($name, $arguments[0]);
        } else if (str_starts_with($name, 'has') && strlen($name) > 3) {
            $name = substr($name, 3);
            return $this->has($name);
        } else {
            throw new \Exception("method $name not exists");
        }
    }

    /**
     * @return array|mixed
     */
    public function getRealContainer(): mixed
    {
        $cid = \Swoole\Coroutine::getCid();
        if ($cid === -1) {
            return $this->container;
        } else {
            $context = \Swoole\Coroutine::getContext($cid);
            if (!isset($context['global'])) {
                return [];
            }
            return $context['global'];
        }
    }
}