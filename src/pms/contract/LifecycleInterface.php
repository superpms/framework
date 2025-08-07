<?php

namespace pms\contract;

use pms\core\boot\Options;

interface LifecycleInterface{

    public static function start(string $rootPath,Options $bootOptions);

}