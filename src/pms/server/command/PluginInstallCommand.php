<?php

namespace pms\server\command;

use pms\annotate\Inject;
use pms\app\Command;
use pms\app\inject\command\InputInject;
use pms\app\inject\command\OutputInject;
use pms\facade\Path;
use pms\helper\File;

class PluginInstallCommand extends Command
{
    protected string $name = "plugin-install";
    protected string $description = "插件安装";
    protected array $validate = [
        'name'=>[
            'type'=>COMMAND_ARGUMENT_TYPE,
            'des'=>'插件名称',
        ]
    ];

    #[Inject(InputInject::class)]
    protected InputInject $input;

    #[Inject(OutputInject::class)]
    protected OutputInject $output;
    public function entry(){
        $name = $this->input->getArgument('name');
        if(empty($name)){
            $this->output->writeLn("请输入插件名称");
            $this->output->end();
        }
        $nameArr = explode('/',$name);
        if(count($nameArr) !== 2){
            $this->output->writeLn("插件名称不正确");
            $this->output->end();
        }
        $path = Path::getPlugins($name."/plugins.json");
        if(!is_file($path)){
            $this->output->writeLn("插件目录不存在");
            $this->output->end();
        }
        $info = File::readFile($path);
        $info = json_decode($info,true);
        if($info === null || $info === false){
            $this->output->writeLn("插件信息不正确");
            $this->output->end();
        }
        if(!isset($info['name'])){
            $this->output->writeLn("插件信息不正确");
            $this->output->end();
        }
        if($name !== $info['name']) {
            $this->output->writeLn("插件名称与安装目录不相符");
            $this->output->end();
        }
        $config = config('--plugins');
        $config = [
            ...$config,
            $name
        ];
        $config = array_unique($config);
        $epStr = "return [\r\n";
        foreach ($config as $k => $v){
            if($k !== 0){
                $epStr .= ",\r\n";
            }
            $epStr .= "    "."'".$v."'";
        }
        $epStr .= "\r\n];";
        $configCode =  "<?php\r\n // 当前已安装的插件（用于插件目录下的config.php文件读取） \r\n$epStr\r\n";
        File::createFile(Path::getPlugins('/plugins.php'),$configCode);
        $this->output->writeArrayBlock([
            $this->output->setBoldStr($this->output->setColorStr(TERMINAL_COLOR_GREEN,"【插件安装成功】")),
            $this->output->setBoldStr("插件名称:").$name,
            $this->output->setBoldStr("插件目录:").Path::getPlugins($name),
        ]);
        $this->output->end();
    }
}