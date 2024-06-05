<?php

namespace pms\server\example\command;

use pms\app\inject\command\InputInject;
use pms\ArrayObjectAccess;

class CommandInput extends ArrayObjectAccess implements InputInject
{

    protected array $argv = [];

    protected string $name = "";

    public function __construct(protected array $validate)
    {
        $argv = $_SERVER['argv'];
        array_shift($argv);
        $this->name = $argv[0];
        array_shift($argv);
        foreach ($argv as $k => $v){
            if(empty($v)){
                unset($argv[$k]);
            }
        }
        $this->argv = $argv;
        $this->init();
    }

    protected array $options = [];
    protected array $optionsMap = [];
    protected array $argumentsMap = [];
    protected array $arguments = [];

    protected array $params = [];

    protected function init(): void{
        $this->initValidate();
        $this->initArgumentAndOption();
        $this->data = array_merge($this->arguments,$this->options);
    }

    protected function initValidate(): void{
        foreach ($this->validate as $key=> $value) {
            if (isset($value['type'])) {
                if( strtoupper($value['type']) === strtoupper(COMMAND_ARGUMENT_TYPE)){
                    $this->arguments[$key] = $value['default'] ?? null;
                    $this->argumentsMap[] = $key;
                }else if(strtoupper($value['type']) === strtoupper(COMMAND_OPTION_TYPE)){
                    $this->options['--'.$key] = $value['default'] ?? null;
                    $this->optionsMap[] = $key;
                }
            }
        }
    }

    protected function initArgumentAndOption(): void{
        $argIndex = 0;
        $jump = false;
        foreach ($this->argv as $k=> $v){
            if($jump){
                $jump = false;
                continue;
            }
            if (str_starts_with($v, '-')) {
                $v = substr($v, 1);
                if (str_starts_with($v, '-')) {
                    $v = substr($v, 1);
                }
                $jump = true;
                if((isset($this->options[$v]) || in_array($v,$this->optionsMap)) && isset($this->argv[$k + 1])){
                    if(str_starts_with($this->argv[$k + 1], '-')){
                        $jump = false;
                    }else{
                        $this->options['--'.$v] = $this->argv[$k + 1];
                    }
                }else if(isset($this->argv[$k + 1]) && str_starts_with($this->argv[$k + 1], '-')){
                    $jump = false;
                }
            }else{
                if(isset($this->argumentsMap[$argIndex])){
                    $this->arguments[$this->argumentsMap[$argIndex]] = $v;
                    $argIndex++;
                }
            }
        }
    }

    public function getArguments(string $name = null)
    {
        if(empty($name)){
            return $this->arguments;
        }
        return $this->data[$name] ?? null;
    }

    public function getOptions(string $name = null)
    {
        if(empty($name)){
            return $this->options;
        }
        return $this->data['--'.$name] ?? null;
    }


}