<?php

use pms\facade\Path;

if (!function_exists('config')) {
    function config(string $name = null, $default = null)
    {
        return \pms\facade\Config::get($name,$default);
    }
}

function customErrorHandler($errno, $errstr, $errfile, int $errline){
    throw new \pms\exception\WarningException($errno, $errstr, $errfile, $errline);
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars):void
    {
        if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) && !headers_sent()) {
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
        if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) || !defined('SWOOLE_VERSION')) {
            exit();
        }else{
            throw new \pms\exception\CliModeForcedInterruptException('');
        }
    }
}

function loadConfig(string $configPath,string $ext='.php'): array
{
    $config = [];
    $files = [];
    if (is_dir($configPath)) {
        $files = glob($configPath . '/*' . $ext);
    }
    $env = "production";
    if (file_exists(Path::getRoot("/dev.lock"))) {
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