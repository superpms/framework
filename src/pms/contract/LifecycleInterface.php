<?php

namespace pms\contract;

interface LifecycleInterface
{

    public static function start(string $rootPath, \pms\core\boot\Options $bootOptions);

}