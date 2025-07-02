<?php
namespace pms\server\example\http\swoole;
use pms\facade\Db;
use pms\facade\RDb;
use pms\server\example\http\Http;

class Example extends Http{

    public function __destruct(){
        try{
            $connector = Db::getInstance();
            if(!empty($connector)){
                foreach ($connector as $value){
                    $value->close();
                }
            }
            $connector = RDb::getInstance();
            if(!empty($connector)){
                foreach ($connector as $value){
                    $value->close();
                }
            }
        }catch (\Throwable $e){
            if(isRunningInConsole()){
                echo "数据库连接错误：".$e->getMessage()."\r\n";
            }
        }
    }
}