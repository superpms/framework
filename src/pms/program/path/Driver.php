<?php

namespace pms\program\path;
/**
 * @method static getRoot(string|array $suffix1 = "",string ...$suffix2);
 * @method static getApp(string|array $suffix1 = "",string ...$suffix2);
 * @method static getConfig(string|array $suffix1 = "",string ...$suffix2);
 * @method static getRuntime(string|array $suffix1 = "",string ...$suffix2);
 */
class Driver
{

    protected array $paths = [];

    /**
     * @param array $data
     * @return void
     */
    public function init(array $data): void{
        foreach ($data as $key => $datum) {
            $this->mount($key, $datum);
        }
    }

    public function mount(string $name,string $path): bool
    {
        $this->paths[strtoupper($name)] = $path;
        $this->makeDir($path);
        return true;
    }

    public function __call(string $name, array $arguments){
        if (str_starts_with($name, 'get') && strlen($name) > 3) {
            $name = strtoupper(substr($name, 3));
            return isset($this->paths[$name]) ? path_join($this->paths[$name], ...$arguments) : null;
        }else if (str_starts_with($name, 'set') && strlen($name) > 3) {
            $name = strtoupper(substr($name, 3));
            return $this->paths[$name] = path_join($arguments);
        }
    }

    protected function makeDir(string $path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

}