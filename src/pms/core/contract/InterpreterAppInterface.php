<?php

namespace pms\core\contract;
interface InterpreterAppInterface{

    /**
     * @return mixed|null 应用入口函数
     */
    public static function run(): mixed;
}