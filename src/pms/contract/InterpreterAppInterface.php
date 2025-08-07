<?php

namespace pms\contract;
use pms\program\boot\Options;

interface InterpreterAppInterface{

    /**
     * @return mixed|null 应用入口函数
     */
    public static function run(Options $bootOptions): mixed;
}