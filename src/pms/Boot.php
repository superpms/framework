<?php

namespace pms;
use pms\core\boot\Options;
use pms\hook\InterpreterHook;
use pms\hook\LifecycleHook;

class Boot
{
    protected Options $bootConfig;

    public function __construct(protected string $rootPath = ""){
        /**
         * 初始系统引导文件
         */
        $this->initBootConfig();
        LifecycleHook::run($this->rootPath);
    }


    protected function initBootConfig(): void{
        $bootFile = path_join($this->rootPath, 'boot.json');
        if (!file_exists($bootFile)) {
            exit("系统引导文件不存在");
        }
        $fileContent = file_get_contents($bootFile);
        if (!json_validate($fileContent)) {
            exit("系统引导文件读取错误");
        }
        $bootConfig = json_decode($fileContent);
        $this->bootConfig = new Options($bootConfig);
    }


    public function __get(string $name){
        InterpreterHook::run($name);
    }
}