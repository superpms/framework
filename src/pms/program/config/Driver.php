<?php

namespace pms\program\config;

use JetBrains\PhpStorm\NoReturn;

class Driver
{

    protected string $development = 'dev';

    protected array $data = [];


    public function mount(string $key, mixed $data): bool
    {
        $this->data[$key] = $data;
        return true;
    }

    public function merge(array $data): bool
    {
        $this->data = array_merge_deep($this->data, $data);
        return true;
    }

    public function set(string $key, string $name, mixed $data): bool{
        if (!isset($this->data[$key])) {
            $this->data[$key] = [];
        }
        $this->data[$key][$name] = $data;
        return true;
    }

    public function get(string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->data;
        }
        $data = array_chain($this->data, $name);
        return $data !== null ? $data : $default;
    }

    public function fetchConfig(string|array $paths): void
    {
        if(is_string($paths)){
            $paths = [$paths];
        }
        $files = [];
        foreach ($paths as $path){
            $devPath = path_join($path, $this->development);
            if(!is_dir($path)){
                continue;
            }
            $files = [
                ...$files,
                ...glob(path_join($path, '*.php')),
                ...glob(path_join($path, '*.ini'))
            ];
            if (is_dev() && is_dir($devPath)){
                $files = [
                    ...$files,
                    ...glob(path_join($devPath, '*.php')),
                    ...glob(path_join($devPath, '*.ini')),
                ];
            }
        }
        $configList = load_file_config($files);
        $this->merge($configList);
    }
}