<?php

namespace pms\server\entry;

use pms\server\request\HttpSwooleRequest;
use pms\server\response\HttpSwooleResponse;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as SwooleServer;

class HttpSwoole extends Common{

    protected string $name = 'http swoole server';
    public function run(){
        $host = config('http.swoole.host','127.0.0.1');
        $port = config('http.swoole.port',9501);
        $setConfig = config('http.swoole.config',[]);
        if(!is_array($setConfig)){
            $setConfig = [];
        }
        $http = new SwooleServer($host, $port);
        $http->set([
            'log_file' => __RUNTIME_PATH__.'/swoole.log',
            ...$setConfig,
            'reload_async'=>true,
        ]);
        $http->on('request', function (Request $request, Response $response){
            $request = new HttpSwooleRequest($request);
            $response = new HttpSwooleResponse($response);
            $pathinfo = $request->pathinfo();
            $appName = config('http.app_name','api');
            if(str_starts_with($pathinfo,'/'.$appName)){
                $httpSwoole = new \pms\server\HttpSwoole();
                $httpSwoole->run($request,$response);
            }else{
                $this->sendFile($pathinfo,$response);
            }
        });
        $this->runLog($host,$port);
        $http->start();
    }

}