<?php

namespace pms\server;
use pms\contract\ServerInterface;
use pms\server\example\command\Example;

class Command implements ServerInterface
{
    protected string $name = 'command server';

    protected static array $command = [

    ];

    public static function run(){
        $argv = $_SERVER['argv'];
        array_shift($argv);
        if(empty($argv)){
            exit("未输入要执行的命令");
        }
        static::$command = [
            ...static::$command,
            ...config('command',[]),
        ];
        $name = $argv[0];
        if(!isset(static::$command[$name])){
            exit("命令不存在");
        }
        (new Example(static::$command,$name))->run();
    }

}