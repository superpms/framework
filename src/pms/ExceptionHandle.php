<?php

namespace pms;

use pms\app\inject\http\ResponseInject;
use pms\contract\ExceptionHandleInterface;
use pms\exception\AuthException;
use pms\exception\ClassNotFoundException;
use pms\exception\FuncNotFoundException;
use pms\exception\RequestMethodException;
use pms\exception\RequestParamsException;
use pms\exception\SystemException;
use pms\exception\WarningException;

class ExceptionHandle implements ExceptionHandleInterface{

    /**
     * 状态码
     * @var array|int[]
     */
    protected array $handleCode = [
        SystemException::class => 500,
        WarningException::class => 500,
        ClassNotFoundException::class => 500,
        FuncNotFoundException::class => 500,
        RequestMethodException::class => 502,
        RequestParamsException::class => 503,
    ];

    protected bool $debug;

    protected mixed $content;
    protected ResponseInject $response;

    final public function getContent(): mixed{
        return $this->content;
    }

    final public function __construct(\Throwable $exception){
        $this->debug = config('app.debug',false);
        $this->content = $this->handle($exception);
    }

    public function handle(\Throwable $exception): array{
        $data = [
            'message' => $exception->getMessage(),
            'code' => $this->handle[get_class($exception)] ?? 500,
        ];
        if($this->debug &&
            (
                $exception instanceof WarningException
                || $exception instanceof ClassNotFoundException
                || $exception instanceof FuncNotFoundException
            )
        ){
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
            $data['trace'] = $exception->getTraceAsString();
        }
        if($exception instanceof RequestParamsException){
            $data= [
                ...$data,
                'field' => $exception->getField(),
                'desc' => $exception->getDesc(),
                'type' => $exception->getType()
            ];
        }
        return $data;
    }
}