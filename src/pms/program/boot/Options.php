<?php
namespace pms\program\boot;
use pms\CfgOptions;


/**
 * @internal 此配置类仅供框架内部使用
 * @final 禁止继承
 * @property string $timezone;
 * @property string $dir_app;
 * @property string $dir_config;
 * @property string $dir_runtime;
 * @property array $autoload;
 * @property boolean $error_debug;
 * @property boolean $log_debug;
 * @property object $php_ini;
 * @property array $php_function;
 * @property array $php_extension;
 * @property object $extend;
 */
class Options extends CfgOptions
{

    public function __construct($boot)
    {
        $this->timezone = $boot?->timezone ?? 'Asia/Shanghai';
        $this->dir_app = $boot?->dir?->app ?? 'app';
        $this->dir_config = $boot?->dir?->config ?? 'config';
        $this->dir_runtime = $boot?->dir?->runtime ?? 'runtime';
        $this->autoload = $boot?->autoload ?? [];
        $this->error_debug = $boot?->error_debug ?? true;
        $this->log_debug = $boot?->log_debug ?? false;
        $this->php_ini = $boot?->php?->ini ?? (object)[];
        $this->php_function = $boot?->php?->function ?? [];
        $this->php_extension = $boot?->php?->extension ?? [];
        $this->extend = $boot?->extend ?? (object)[];
    }
}