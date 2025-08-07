<?php

namespace pms\contract;

interface InterpreterAppInterface{

    /**
     * @return mixed|null 应用入口函数
     */
    public static function run(\pms\program\boot\Options $bootOptions): mixed;
}