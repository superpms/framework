<?php

namespace pms\core\driver\plugin;

use pms\exception\SystemException;
use pms\facade\Config;
use pms\facade\Path;
use pms\helper\Data;

class Driver{

    protected string $rootPath;
    protected string $configKey = '--pms-plugins-installed';

    protected array $config = [];
    protected array $plugins = [];

    public function init(string $rootPath): void
    {
        $this->rootPath = $rootPath;

        /**
         * 加载插件注册配置
         */
        $installedFile = path_join($this->rootPath,"/installed.php");
        if (is_file($installedFile)) {
            $pluginsConfig = include $installedFile;
            Config::set($this->configKey, $pluginsConfig);
        }

        /**
         * 加载插件全局方法
         */
        foreach (config($this->configKey, []) as $item) {
            $autoloadFile = path_join($this->rootPath,$item,"/autoload.php");
            if (is_file($autoloadFile)) {
                include_once $autoloadFile;
            }
        }
    }

    public function config(string $pluginName,string $name = null, $default = null){
        $config = $this->getPluginConfig($pluginName);
        if ($name === null) {
            return $config;
        }
        $data = Data::getChainData($config,$name);
        return $data !== null ? $data : $default;
    }

    public function getName(string $class): string{
        $pluginName = explode("\\",$class);
        $pluginName = array_slice($pluginName,1,2);
        return implode("/",$pluginName);
    }

    private function getPluginConfig($class){
        $pluginName = $this->getName($class);
        if(isset($this->config[$pluginName])){
            return $this->config[$pluginName];
        }
        if(empty($this->plugins)){
            $this->plugins = config($this->configKey);
        }
        if(!in_array($pluginName,$this->plugins)){
            throw new SystemException("$pluginName:插件未安装",502);
        }
        $configPath = path_join($this->rootPath,$pluginName,"/config.php");
        if(!is_file($configPath)){
            $this->config[$pluginName] = [];
            return[];
        }else{
            $this->config[$pluginName] = include $configPath;
            return $this->config[$pluginName];
        }
    }

}