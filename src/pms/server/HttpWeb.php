<?php

namespace pms\server;
use pms\contract\ServerInterface;
use pms\server\example\http\web\WebHttpRequest;
use pms\server\example\http\web\WebHttpResponse;
use pms\server\example\http\web\Example;

class HttpWeb implements ServerInterface{
    protected string $name = 'http-web server';

    public static function run(){
        $request = new WebHttpRequest();
        $response = new WebHttpResponse();
        (new Example($request,$response))->run();
    }

}