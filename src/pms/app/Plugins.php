<?php

namespace pms\app;

use pms\facade\Path;
use pms\facade\PluginsConfig;

trait Plugins{

    final public static function config(string $name = null, $default = null){
        return PluginsConfig::config(get_called_class(),$name,$default);
    }

    final public static function path($suffix = null): string{
        if(!empty($suffix) && !str_starts_with($suffix,"/")){
            $suffix = "/".$suffix;
        }
        $name = explode("\\",get_called_class());
        $name = array_slice($name,1,2);
        $name = implode("\\",$name);
        return Path::getPlugins($name.$suffix);
    }

}