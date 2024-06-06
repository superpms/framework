<?php

namespace pms\server\example\command;

use pms\app\inject\command\OutputInject;

class CommandOutput implements OutputInject
{


    public static function write(string $str, string ...$args): void
    {
        echo $str;
        foreach ($args as $arg) {
            static::write($arg);
        }
    }

    public static function print(string $str, string ...$args): void
    {
        static::write($str, ...$args);
    }

    public static function writeLn(string $str, string ...$args): void
    {
        static::write($str);
        echo "\r\n";
        foreach ($args as $arg) {
            static::writeLn($arg);
        }
    }

    public static function printLn(string $str, string ...$args): void
    {
        static::writeLn($str, ...$args);
    }

    public static function writeJsonStr(array $data, array ...$args): void
    {
        static::write(json_encode($data, 320));
        foreach ($args as $arg) {
            static::writeJsonStr($arg);
        }
    }

    public static function printJsonStr(array $data, array ...$args): void
    {
        static::writeJsonStr($data, ...$args);
    }

    public static function writeJsonStrLn(array $data, array ...$args): void
    {
        static::printJsonStr($data);
        echo "\r\n";
        foreach ($args as $arg) {
            static::writeJsonStrLn($arg);
        }
    }

    public static function printJsonStrLn(array $data, array ...$args): void
    {
        static::writeJsonStrLn($data, ...$args);
    }

    public static function writeJsonArray(array $data, array ...$args): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                static::writeJsonStrLn($value);
            } else {
                static::writeLn("$key: $value");
            }
        }
        foreach ($args as $arg) {
            static::writeJsonArray($arg);
        }
    }

    public static function printJsonArray(array $data, array ...$args): void
    {
        static::writeJsonArray($data, ...$args);
    }

    public static function end(string $str = ""): void
    {
        exit($str);
    }
    public static function setColorStr($colorCode,string $str): string
    {
        return "\033[{$colorCode}m".$str."\033[0m";
    }
    public static function setBoldStr(string $str): string
    {
        return "\033[1m".$str."\033[22m";
    }

    public static function writeArrayBlock(array $array, array ...$args): void{
        self::writeLn("");
        $maxLength = 0;
        foreach ($array as $value) {
            $length = mb_strwidth($value);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        $maxLength += 8;
        self::writeLn("|" . str_repeat("-", $maxLength) . "|");
        self::writeLn("|" . str_repeat(" ", $maxLength) . "|");
        foreach ($array as $value) {
            $strWidth = mb_strwidth($value);
            $pattern = "|\033\[[0-9]{1,2}m|";
            preg_match_all($pattern, $value, $matches);
            $allColor = join($matches[0]);
            $strWidth -= mb_strwidth($allColor);
            self::writeLn("|    " . $value . str_repeat(" ", $maxLength - $strWidth - 4) . "|");
        }
        self::writeLn("|" . str_repeat(" ", $maxLength) . "|");
        self::writeLn("|" . str_repeat("-", $maxLength) . "|");
        foreach ($args as $arg){
            self::writeArrayBlock($arg);
        }
    }
}