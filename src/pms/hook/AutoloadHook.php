<?php

namespace pms\hook;

use pms\contract\HookInterface;

class AutoloadHook implements HookInterface
{

    public static array $container = [];

    public static function mount(string|array|\Closure $filePaths): bool
    {
        if (is_string($filePaths) || $filePaths instanceof \Closure) {
            $filePaths = [$filePaths];
        }
        foreach ($filePaths as $filePath) {
            if(is_string($filePath)){
                if (file_exists($filePath)) {
                    self::$container[] = $filePath;
                } else {
                    throw new \Exception("file not exists: $filePath");
                }
            }

        }
        return false;
    }

    public static function run(): void
    {
        foreach (self::$container as $filePath) {
            if ($filePath instanceof \Closure) {
                $filePath();
            } else if (is_string($filePath) && file_exists($filePath)) {
                require_once $filePath;
            }
        }
    }
}