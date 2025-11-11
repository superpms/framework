<?php

namespace pms;
use pms\hook\InterpreterHook;
use pms\hook\LifecycleHook;
use pms\program\boot\Options;

class Boot
{
    protected Options $bootOptions;

    public function __construct(protected string $rootPath = ""){
		if(empty($rootPath)){
			// 向上4层
			$this->rootPath = path_join(dirname(__DIR__),'..','..','..','..');
		}
        set_error_handler('custom_error_handler');
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
        $this->bootOptions = new Options($bootConfig);

        LifecycleHook::run(LIFECYCLE_BOOT,$this->rootPath,$this->bootOptions);
    }



    public function __get(string $name){
        InterpreterHook::run($name,$this->bootOptions);
    }
}