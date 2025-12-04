<?php

namespace pms;

use pms\facade\BootOptions;
use pms\hook\InterpreterHook;
use pms\hook\LifecycleHook;
use pms\program\boot\Driver;

class Boot
{
    protected Driver $bootOptions;

    public function __construct(protected string $rootPath = "")
    {

        if (empty($this->rootPath)) {
            $vendorPath = realpath(dirname(__DIR__));
            // 向上4层
            $this->rootPath = rtrim(dirname($vendorPath, 4), DIRECTORY_SEPARATOR);
        }else{
            $this->rootPath = path_join($this->rootPath);
        }

        set_error_handler(function ($errno, $errstr, $errfile, int $errline) {
            pms_error_set(new \pms\exception\ErrorException($errno, $errstr, $errfile, $errline));
        });
        set_exception_handler(function($exception) {
            pms_error_set($exception);
        });

        try{
            /**
             * 初始系统引导文件
             */
            $bootFile = path_join($this->rootPath, 'boot.json');
            if (!file_exists($bootFile)) {
                exit("系统引导文件不存在");
            }
            $fileContent = file_get_contents($bootFile);
            if (!json_validate($fileContent)) {
                exit("系统引导文件读取错误");
            }
            $bootConfig = json_decode($fileContent);

            BootOptions::init($bootConfig);

            LifecycleHook::run(LIFECYCLE_BOOT, $this->rootPath);
        }catch (\Throwable $exception){
            pms_error_set($exception);
        }
    }


    public function __get(string $name)
    {
        try{
            InterpreterHook::run($name);
        }catch (\Throwable $exception){
            pms_error_set($exception);
        }
        $this->handleError();
    }


    public function handleError(): void
    {
        $error = pms_error();
        if($error !== null){
            print_r("无任何后置方法处理此异常，最终由Boot::__destruct抛出");
            pms_error_clear();
            throw $error;
        }
    }


    public function __destruct()
    {
        $this->handleError();
    }
}