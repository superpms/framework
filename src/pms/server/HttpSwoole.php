<?php

namespace pms\server;

use pms\contract\ServerInterface;
use pms\facade\Db;
use pms\facade\Path;
use pms\facade\RDb;
use pms\server\example\http\swoole\SwooleHttpRequest;
use pms\server\example\http\swoole\SwooleHttpResponse;
use pms\server\example\http\swoole\Example;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class HttpSwoole implements ServerInterface{

    protected string $name = 'http-swoole server';

    public static function run(){
        Db::isPool(true);
        RDb::isPool(true);
        $host = config('http.swoole.host','127.0.0.1');
        $port = config('http.swoole.port',9501);
        $setConfig = config('http.swoole.config',[]);
        if(!is_array($setConfig)){
            $setConfig = [];
        }
        $http = new Server($host, $port);
        $http->set([
            'log_file' => Path::getRuntime('/swoole.log'),
            ...$setConfig,
            'reload_async'=>true,
        ]);
        $http->on('request', function (Request $request, Response $response){
            $request = new SwooleHttpRequest($request);
            $response = new SwooleHttpResponse($response);
            (new Example($request,$response))->run();
        });
        $http->start();
    }

}