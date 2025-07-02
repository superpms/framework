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
        self::customShutDownHandler($response);
        (new Example($request,$response))->run();
    }

    public static function customShutDownHandler($response): void{
        register_shutdown_function(function ()use($response) {
            $error = error_get_last();
            if (!empty($error)) {
                ob_end_clean();
                $response->status(500, 'Server Error');
                if (config('app.debug')) {
                    $response->header("content-type", JSON_CONTENT_TYPE);
                    $response->end(json_encode($error));
                } else {
                    $response->end();
                }
            }
        });
    }
}