<?php

namespace pms\contract;

interface InterpreterAppInterface{

    /**
     * @return mixed|null 应用入口函数
     */
    public static function entry(): mixed;
}