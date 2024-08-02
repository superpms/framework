<?php

namespace pms;

use pms\app\inject\http\ResponseInject;
use pms\contract\ExceptionHandleInterface;
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
    protected array $handle = [
        SystemException::class,
        WarningException::class,
        ClassNotFoundException::class,
        FuncNotFoundException::class,
        RequestMethodException::class,
        RequestParamsException::class,
    ];

    protected bool $debug;

    protected mixed $content;
    protected ResponseInject $response;

    final public function getContent(): mixed{
        return $this->content;
    }

    final public function __construct(\Throwable $exception,\Closure $statusCode){
        $this->debug = config('app.debug',false);
        $this->content = $this->handle($exception,$statusCode);
    }

    public function handle(\Throwable $exception, \Closure $statusCode): array{
        if($exception instanceof SystemException){
            $statusCode(500);
            $data = [
                'message'=>$exception->getMessage(),
                'code' => $exception->getCode(),
            ];
        }else if(
            $exception instanceof ClassNotFoundException
            || $exception instanceof FuncNotFoundException
        ){
            $statusCode(500);
            $data = [
                'message'=>'类或方法不存在',
                'code' => 504,
            ];
            if($this->debug){
                $data['message'] = $exception->getMessage();
                $data['file'] = $exception->getFile();
                $data['line'] = $exception->getLine();
                $data['trace'] = $exception->getTrace();
            }
        }else if ($exception instanceof RequestMethodException){
            $data = [
                'message' => $exception->getMessage(),
                'code' => 400,
            ];
        }else if($exception instanceof RequestParamsException){
            $data= [
                'message' => $exception->getMessage(),
                'code' => 401,
                'field' => $exception->getField(),
                'desc' => $exception->getDesc(),
                'type' => $exception->getType(),
                'val' => $exception->getVal(),
            ];
        }else{
            $data = [
                'message' => $exception->getMessage(),
                'code' => 500,
            ];
            if($this->debug){
                $data['message'] = $exception->getMessage();
                $data['file'] = $exception->getFile();
                $data['line'] = $exception->getLine();
                $data['trace'] = $exception->getTrace();
            }
        }
        return $data;
    }
}