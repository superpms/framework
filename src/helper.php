<?php

use pms\facade\Path;

function customErrorHandler($errno, $errstr, $errfile, int $errline){
    throw new \pms\exception\WarningException($errno, $errstr, $errfile, $errline);
}

if(!function_exists('load_file_config')){
    function load_file_config(string $configPath,string $ext='.php'): array
    {
        $config = [];
        $files = [];
        if (is_dir($configPath)) {
            $files = glob($configPath . '/*' . $ext);
        }
        $env = "production";
        if (isDev()) {
            $env = "development";
        }
        $environmentPath = $configPath . DIRECTORY_SEPARATOR . $env;
        if (is_dir($environmentPath)) {
            $files = array_merge($files, glob($environmentPath . '/*.php'));
        }
        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (is_file($file)) {
                $filename = $file;
            } elseif (is_file($configPath . $file . $ext)) {
                $filename = $configPath . $file . $ext;
            }
            $config[strtolower($name)] = include $file;
        }
        return $config;
    }
}else{
    throw new \Exception("PMS framework 核心函数 load_file_config 被重载");
}

if (!function_exists('config')) {
    function config(string $name = null, $default = null)
    {
        return \pms\facade\Config::get($name,$default);
    }
}
if (!function_exists('isDev')) {
    function isDev():bool
    {
        return file_exists(Path::getRoot("/dev.lock"));
    }
}
if (!function_exists('dd')) {
    function dd(mixed ...$vars):void
    {
        if (!isRunningInConsole() && !headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/html');
        }
        if (array_key_exists(0, $vars) && 1 === count($vars)) {
            \Symfony\Component\VarDumper\VarDumper::dump($vars[0]);
        } else {
            foreach ($vars as $k => $v) {
                \Symfony\Component\VarDumper\VarDumper::dump($v, is_int($k) ? 1 + $k : $k);
            }
        }
        if(!isRunningInConsole()){
            die;
        }else{
            throw new \pms\exception\CliModeForcedInterruptException("die");
        }
    }
}
if(!function_exists('isRunningInConsole')){
    function isRunningInConsole(): bool{
        return in_array(PHP_SAPI, ['cli', 'phpdbg','embed'], true);
    }
}
if(!function_exists('path_join')){
    function path_join(...$segments): string{
        $symbol = DIRECTORY_SEPARATOR;
        $noSymbol = $symbol === '/' ? '\\' : '/';
        $path = array_map(function ($segment) use ($symbol, $noSymbol) {
            if (is_array($segment)) {
                $segment = path_join(...$segment);
            }
            return str_replace($noSymbol, $symbol, $segment);
        }, $segments);

        $parts = explode($symbol, join($symbol, $path));
        $stack = [];

        foreach ($parts as $key=> $part) {
            // 忽略空段和当前目录
            if (($key !== 0 && $part === '') || $part === '.') continue;

            // 处理上级目录
            if ($part === '..') {
                if (!empty($stack)) array_pop($stack);
                continue;
            }

            $stack[] = $part;
        }
        // 重新组合路径
        return implode($symbol, $stack);
    }
}
if(!function_exists('path_class')){
    function path_class(string $path): string{
        $path = str_replace(DIRECTORY_SEPARATOR, '\\', $path);
        return str_replace('.php', '', $path);
    }
}