<?php

namespace pms;

class PathManager
{

    protected string $server;
    protected string $root;
    protected string $app;
    protected string $core;
    protected string $config;
    protected string $plugins;
    protected string $runtime;
    protected string $public;
    protected string $vendor;

    /**
     * @param $key
     * @return void
     */
    public function init(array $data): void
    {
        foreach ($data as $key => $datum) {
            $this->$key = $datum;
        }
    }

    protected function convertSeparate(string|array $string): string{

        if(is_array($string)){
            $string = join(DIRECTORY_SEPARATOR,$string);
        }
        $string = str_replace('\\\\', "\\", $string);
        $string = str_replace('//', "/", $string);
        if (DIRECTORY_SEPARATOR === '/') {
            $string = str_replace('\\', DIRECTORY_SEPARATOR, $string);
        } else {
            $string = str_replace('/', DIRECTORY_SEPARATOR, $string);
        }
        if(!str_starts_with($string,DIRECTORY_SEPARATOR)){
            $string = DIRECTORY_SEPARATOR . $string;
        }
        return $string;
    }

    public function getServer(string|array $suffix = ""): string
    {
        return $this->server . $this->convertSeparate($suffix);
    }

    public function getRoot(string|array $suffix = ""): string
    {
        return $this->root . $this->convertSeparate($suffix);
    }

    public function getApp(string|array $suffix = ""): string
    {
        return $this->app . $this->convertSeparate($suffix);
    }

    public function getCore(string|array $suffix = ""): string
    {
        return $this->core . $this->convertSeparate($suffix);
    }

    public function getConfig(string|array $suffix = ""): string
    {
        return $this->config . $this->convertSeparate($suffix);
    }

    public function getPlugins(string|array $suffix = ""): string
    {
        return $this->plugins . $this->convertSeparate($suffix);
    }

    public function getRuntime(string|array $suffix = ""): string
    {
        return $this->runtime . $this->convertSeparate($suffix);
    }

    public function getPublic(string|array $suffix = ""): string
    {
        return $this->public . $this->convertSeparate($suffix);
    }

    public function getVendor(string|array $suffix = ""): string
    {
        return $this->vendor . $this->convertSeparate($suffix);
    }

}