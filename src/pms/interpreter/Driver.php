<?php

namespace pms\interpreter;

use pms\app\InterpreterApp;

class Driver{

    protected array $interpreter = [];

    public function install(string $serverName, string $serverClass): bool{
        if(!isset($this->interpreter[$serverName])){
            $this->interpreter[$serverName] = $serverClass;
            return true;
        }
        return false;
    }

    public function run(string $serverName): mixed{
        if (!isset($this->interpreter[$serverName])) {
            exit("解释器 [{$serverName}] 未安装");
        }
        /**
         * @var InterpreterApp $server;
         */
        $server = $this->interpreter[$serverName];
        return $server::run();
    }

}