<?php

namespace pms\contract;

interface LifecycleInterface
{

    public static function start(string $rootPath, \pms\program\boot\Options $bootOptions);

}