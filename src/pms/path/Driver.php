<?php

namespace pms\path;

class Driver
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
    protected string $symbol = '';
    protected string $noSymbol = '';
    protected string $dSymbol = '';
    public function __construct(){
        $this->symbol = DIRECTORY_SEPARATOR;
        if($this->symbol === '/'){
            $this->noSymbol = "\\";
            $this->dSymbol = "/\\\\+/";
        }else{
            $this->noSymbol = "/";
            $this->dSymbol = "/\/+/";
        }
    }

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
        if($string === ""){
            return "";
        }
        $dSymbol = "$this->symbol$this->symbol";
        if(is_array($string)){
            $string = join($this->symbol,$string);
        }
        if(str_contains($string,$this->noSymbol)){
            $string = str_replace($this->noSymbol, $this->symbol, $string);
        }
        // 正则匹配 连续多个 $this->symbol 替换为一个 $this->symbol
        if(str_contains($string,$dSymbol)){
            $string = preg_replace($this->dSymbol, $this->symbol, $string);
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