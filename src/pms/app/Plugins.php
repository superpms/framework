<?php

namespace pms\app;

use pms\facade\Path as PathManager;
use pms\facade\Plugins as Manager;

trait Plugins{

    final public static function config(string $name = null, $default = null){
        return Manager::config(get_called_class(),$name,$default);
    }

    final public static function path($suffix = null): string{
        if(!empty($suffix) && !str_starts_with($suffix,"/")){
            $suffix = "/".$suffix;
        }
        $name = explode("\\",get_called_class());
        $name = array_slice($name,1,2);
        $name = implode("\\",$name);
        return PathManager::getPlugins($name.$suffix);
    }

}