<?php

namespace pms\server;

use pms\contract\ApplicationActionInterface;
use pms\contract\MiddlewareInterface;
use pms\exception\ClassNotFoundException;
use pms\exception\SystemException;
use pms\inject\Request;
use pms\inject\Response;
use pms\server\middleware\ValidateHttpRequestMiddleware;
use ReflectionClass;
use pms\helper\Data;

abstract class Http extends Common
{
    public function __construct()
    {
        $this->app = config('http.app_name');
    }

    protected array $middlewares = [
        ValidateHttpRequestMiddleware::class
    ];

    protected string $contentType;

    protected function middleware(string $classNamespace): ReflectionClass{
        try {
            $actionClass = $this->getClass($classNamespace);
        } catch (\Throwable $e) {
            throw new ClassNotFoundException($classNamespace, $e);
        }
        // 执行应用全局中间件
        $this->runMiddleware($this->middlewares,[
            'class'=>$actionClass,
            'request'=>$this->request,
        ]);
        // 执行应用独立中间件
        $actionMiddlewares = $actionClass->getProperty('middleware')->getDefaultValue();
        $this->runMiddleware($actionMiddlewares,[
            'class'=>$actionClass,
            'request'=>$this->request,
        ]);
        return $actionClass;
    }

    /**
     * 执行中间件
     * @param array|string $middlewares
     * @param array $args
     * @return void
     */
    protected function runMiddleware(array|string $middlewares,array $args): void
    {
        if(is_string($middlewares)){
            $middlewares = [$middlewares];
        }
        foreach ($middlewares as $item){
            /**
             * @var $obj MiddlewareInterface
             */
            if(class_exists($item)){
                $mClass = $this->getClass($item);
                $obj = $this->invokeClass($mClass,$args);
                $obj->handle();
                if($mClass->hasMethod('callback')){
                    $obj->callback($this);
                }
            }else{
                throw new SystemException("middleware is not found:".$item);
            }
        }
    }

    public function run(Request $request, Response $response): void{
        try {
            $this->request = $request;
            $this->put('pms\inject\Request', $this->request);
            $this->response = $response;
            $responseHeader = config('web.response_header', []);
            foreach ($responseHeader as $key => $value) {
                $this->response->header($key, $value);
            }
            $this->put('pms\inject\Response', $this->response);
            if ($this->request->method() === 'OPTIONS') {
                $this->response->end();
            }
            $pathinfo = $this->request->pathinfo();
            $defaultPath = [
                '/' . $this->app,
                '/' . $this->app . "/",
            ];
            if (in_array($pathinfo, $defaultPath)) {
                $defaultController = config('http.default_controller', 'Index');
                $pathinfo = DIRECTORY_SEPARATOR . $this->app . DIRECTORY_SEPARATOR . $defaultController;
            }
            $data = $this->execute($pathinfo, function (ReflectionClass $class, ApplicationActionInterface $obj) {
                $contentType = $class->getProperty('contentType');
                $this->contentType = $contentType->getValue($obj);
            });

            $data = $this->contentToString($data, $this->contentType);

            if ($response->isWritable()) {
                $this->response->header('Content-Type', $this->contentType);
                $response->end($data);
            }
        } catch (\Throwable $e) {
            $this->exceptionHandle($e);
        }
    }

    protected function contentToString(mixed $data, string $contentType){
        if (is_string($data)) {
            return $data;
        }
        return match ($contentType) {
            JSON_CONTENT_TYPE => json_encode($data),
            JSONP_CONTENT_TYPE => $this->request->get('callback', 'callback') . '(' . json_encode($data) . ')',
            XML_CONTENT_TYPE => Data::arrayToXml($data),
            default => $data,
        };
    }

}