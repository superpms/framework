<?php

namespace pms\hook;

use pms\contract\HookInterface;
use pms\facade\Path;

class AutoloadHook implements HookInterface
{

    protected static array $container = [];

    public static function mount(string|array|\Closure $filePaths): bool
    {
        if (is_string($filePaths) || $filePaths instanceof \Closure) {
            $filePaths = [$filePaths];
        }
        foreach ($filePaths as $filePath) {
            if(is_string($filePath)){
                $reFilePath = Path::getRoot($filePath);
                if($reFilePath){
                    static::$container[] = $reFilePath;
                }else if (is_file($filePath)) {
                    static::$container[] = $filePath;
                }else {
                    throw new \Exception("file not exists: $filePath");
                }
            }else if($filePath instanceof \Closure){
                static::$container[] = $filePath;
            }

        }
        return false;
    }

    public static function run(): void
    {
        foreach (static::$container as $filePath) {
            if ($filePath instanceof \Closure) {
                $filePath();
            } else if (is_string($filePath) && file_exists($filePath)) {
                require_once $filePath;
            }
        }
    }

    public static function audit(): array
    {
        return static::$container;
    }
}