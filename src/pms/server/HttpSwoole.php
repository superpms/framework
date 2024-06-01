<?php
namespace pms\server;
use pms\facade\RDb;
use pms\server\request\HttpSwooleRequest;
use pms\server\request\SafeParams;
use pms\server\response\HttpSwooleResponse;
use pms\facade\Db;
class HttpSwoole extends Http
{

    protected bool $connectionPool = true;

    protected array $bind = [
        'pms\inject\Request' => HttpSwooleRequest::class,
        'pms\inject\Response' => HttpSwooleResponse::class,
        'pms\inject\SafeParams' => SafeParams::class,
    ];

    public function __destruct(){
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
    }
}