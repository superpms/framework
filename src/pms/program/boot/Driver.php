<?php

namespace pms\program\boot;

use pms\OptionsAccessCfg;


/**
 * @internal 此配置类仅供框架内部使用
 * @final    禁止继承
 * @method static \string  get_timezone  获取时区
 * @method static \string  get_dir_app  获取应用目录
 * @method static \string  get_dir_config  获取配置目录
 * @method static \string  get_dir_runtime  获取运行时目录
 * @method static \array get_autoload  获取自动加载配置
 * @method static \boolean get_error_debug  获取是否开启错误调试
 * @method static \boolean get_log_debug  获取是否开启日志调试
 * @method static \object  get_php  获取 PHP 配置
 * @method static \object  get_php_ini  获取 PHP 运行配置
 */
class Driver extends OptionsAccessCfg
{
    protected const missing = false;

    public function init($boot)
    {
        $this->timezone = $boot?->timezone ?? 'Asia/Shanghai';
        $this->dir_app = $boot?->dir?->app ?? 'app';
        $this->dir_config = $boot?->dir?->config ?? 'config';
        $this->dir_runtime = $boot?->dir?->runtime ?? 'runtime';
        $this->autoload = $boot?->autoload ?? [];
        $this->error_debug = $boot?->error_debug ?? true;
        $this->log_debug = $boot?->log_debug ?? false;
        $this->php = $boot?->php ?? (object)[];
        $this->php_ini = $boot?->php?->ini ?? (object)[];
        $this->extend = $boot?->extend ?? (object)[];
    }

    /**
     * 获取扩展配置
     * @param string $extend
     * @return null|mixed
     */
    public function get_extend(string $extend, mixed $default = null): mixed
    {
        return $this->extend->$extend ?? $default;
    }

}