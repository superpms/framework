<?php

namespace pms;

use pms\exception\SystemException;
use pms\facade\Path;
use pms\helper\Data;

class PluginsManager{

    protected array $config = [];

    protected array $plugins = [];

    public function config(string $pluginName,string $name = null, $default = null){
        $config = $this->getPluginConfig($pluginName);
        if ($name === null) {
            return $config;
        }
        $data = Data::getChainData($config,$name);
        return $data !== null ? $data : $default;
    }

    private function getPluginConfig($class){
        $pluginName = explode("\\",$class);
        $pluginName = array_slice($pluginName,1,2);
        $pluginName = implode("/",$pluginName);
        if(isset($this->config[$pluginName])){
            return $this->config[$pluginName];
        }
        if(empty($this->plugins)){
            $this->plugins = config('--plugins');
        }
        if(!in_array($pluginName,$this->plugins)){
            throw new SystemException("$pluginName:插件未安装");
        }
        $path = Path::getPlugins($pluginName."/config.php");
        if(!is_file($path)){
            $this->config[$pluginName] = [];
            return[];
        }else{
            $this->config[$pluginName] = include $path;
            return $this->config[$pluginName];
        }
    }

}