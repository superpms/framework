<?php

namespace pms\app;


abstract class PluginSetup
{
    /**
     * 插件安装Hook
     * @return void
     */
    public function install(): void{}

    /**
     * 插件卸载Hook
     * @return void
     */
    public function uninstall(): void{}

}