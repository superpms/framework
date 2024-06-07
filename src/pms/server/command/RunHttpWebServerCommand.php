<?php

namespace pms\server\command;

use pms\annotate\Inject;
use pms\app\Command;
use pms\app\inject\command\InputInject;
use pms\app\inject\command\OutputInject;
use pms\facade\Path;

class RunHttpWebServerCommand extends Command
{
    protected string $name = "PHP Built-in Server";
    protected string $description = "插件安装";
    protected array $validate = [
        'host'=>[
            'type'=>COMMAND_OPTION_TYPE,
            'des'=>'绑定地址',
            'default'=>'0.0.0.0'
        ],
        'port'=>[
            'type'=>COMMAND_OPTION_TYPE,
            'des'=>'绑定端口',
            'default'=>'8000'
        ],
        'root'=>[
            'type'=>COMMAND_OPTION_TYPE,
            'des'=>'绑定端口',
            'default'=>''
        ]
    ];

    #[Inject(InputInject::class)]
    protected InputInject $input;

    #[Inject(OutputInject::class)]
    protected OutputInject $output;
    public function entry(){
        $host = $this->input->getOption('host');
        $port = $this->input->getOption('port');
        $root = $this->input->getOption('root');
        if (empty($root)) {
            $root = Path::getPublic();
        }
        $command = sprintf(
            '%s -S %s:%d -t %s %s',
            PHP_BINARY,
            $host,
            $port,
            escapeshellarg($root),
            escapeshellarg($root . DIRECTORY_SEPARATOR . 'index.php')
        );
        $this->output->writeArrayBlock([
            $this->output->setBoldStr($this->output->setColorStr(TERMINAL_COLOR_GREEN,"● PHP 内置服务器")),
            '服务IP: '.$host,
            '服务端口: '.$port,
            sprintf('服务根目录: %s', $root),
            sprintf('服务地址: <http://%s:%s/>', $host, $port),
            sprintf('本机访问地址: <http://127.0.0.1:%s/>', $port),
            "\033[31m使用\033[1m`CTRL-C`\033[22m即可退出服务\033[0m",
        ]);
        passthru($command);
    }
}