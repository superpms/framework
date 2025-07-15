<?php

namespace pms\core\driver\path;
/**
 * @method static getRoot(string|array $suffix1 = "",...$suffix2);
 * @method static getServer(string|array $suffix1 = "",...$suffix2);
 * @method static getApp(string|array $suffix1 = "",...$suffix2);
 * @method static getCore(string|array $suffix1 = "",...$suffix2);
 * @method static getConfig(string|array $suffix1 = "",...$suffix2);
 * @method static getPlugins(string|array $suffix1 = "",...$suffix2);
 * @method static getRuntime(string|array $suffix1 = "",...$suffix2);
 * @method static getPublic(string|array $suffix1 = "",...$suffix2);
 * @method static getVendor(string|array $suffix1 = "",...$suffix2);
 */
class Driver
{

    protected array $paths = [];

    /**
     * @param array $data
     * @return void
     */
    public function init(array $data): void
    {
        foreach ($data as $key => $datum) {
            $this->paths[strtoupper($key)] = $datum;
        }
    }

    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'get') && strlen($name) > 3) {
            $name = strtoupper(substr($name, 3));
            return isset($this->paths[$name]) ? path_join($this->paths[$name], ...$arguments) : null;
        }else if (str_starts_with($name, 'set') && strlen($name) > 3) {
            $name = strtoupper(substr($name, 3));
            return $this->paths[$name] = path_join($arguments);
        }
    }

}