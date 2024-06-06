<?php

namespace pms\app\inject\command;

interface OutputInject
{
    public static function write(string $str,string ...$args): void;
    public static function print(string $str,string ...$args): void;
    public static function writeLn(string $str,string ...$args): void;
    public static function printLn(string $str,string ...$args): void;
    public static function writeJsonStr(array $data,array ...$args): void;
    public static function printJsonStr(array $data,array ...$args): void;
    public static function writeJsonStrLn(array $data,array ...$args): void;
    public static function printJsonStrLn(array $data,array ...$args): void;
    public static function writeJsonArray(array $data,array ...$args): void;
    public static function printJsonArray(array $data,array ...$args): void;
    public static function end(string $str = ""):void;
    public static function setColorStr($colorCode,string $str): string;
    public static function setBoldStr(string $str): string;
    public static function writeArrayBlock(array $array, array ...$args): void;
}