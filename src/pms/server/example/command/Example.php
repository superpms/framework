<?php

namespace pms\server\example\command;

use pms\app\inject\command\InputInject;
use pms\app\inject\command\OutputInject;
use pms\contract\AppInterface;
use pms\server\example\Server;

class Example extends Server
{
    protected array $command = [];
    protected string $name = "";
    public function __construct(array $command,string $name){
        $this->command = $command;
        $this->name = $name;
    }

    public function run(): void{
        $namespace = $this->command[$this->name];
        $class = $this->getClass($namespace);
        $validate = $class->getProperty('validate')->getDefaultValue();
        $input = new CommandInput($validate);
        $this->put(InputInject::class,$input);
        $this->put(OutputInject::class,CommandOutput::class);
         /**
         * @var $obj AppInterface
         */
        $obj = $this->invokeClass($namespace,[
            $this->command
        ]);
        $obj->entry();
    }
}