<?php

namespace pms\program\config;

use pms\facade\Ctx;

class Driver
{

    protected string $development = 'dev';
    protected array $data = [];
    protected array $coroutine = [];

    protected string $coroutineConfigKey = '__coroutine_config__';

    public function mount(string $key, mixed $data): bool{
        $this->data[$key] = $data;
        return true;
    }

    public function merge(array $data,bool $inCoroutine = false): bool{
        if(!$inCoroutine){
            $this->data = array_merge_deep($this->data, $data);
        }else{
            Ctx::set($this->coroutineConfigKey,array_merge_deep(Ctx::get($this->coroutineConfigKey,[]), $data));
        }
        return true;
    }

    public function set(string $key, string $name, mixed $data): bool{
        if (!isset($this->data[$key])) {
            $this->data[$key] = [];
        }
        $this->data[$key][$name] = $data;
        return true;
    }

    public function get(?string $name = null,mixed $default = null)
    {
        $data = Ctx::get('config', array_merge_deep($this->data, Ctx::get($this->coroutineConfigKey,[])));
        if ($name === null) {
            return $data;
        }
        $data = object_chain($data, $name);
        return $data !== null ? $data : $default;
    }

    public function fetchConfig(string|array $paths,bool $inCoroutine = false): void{
        if(is_string($paths)){
            $paths = [$paths];
        }
        $files = [];
        foreach ($paths as $path){
            if(!is_dir($path)){
                continue;
            }
            $files = [
                ...$files,
                ...glob(path_join($path, '*.php')),
                ...glob(path_join($path, '*.ini'))
            ];
            $devPath = path_join($path, $this->development);
            if (is_dev() && is_dir($devPath)){
                $files = [
                    ...$files,
                    ...glob(path_join($devPath, '*.php')),
                    ...glob(path_join($devPath, '*.ini')),
                ];
            }
        }
        $configList = load_file_config($files);
        $this->merge($configList,in_swoole() && $inCoroutine);
    }

}