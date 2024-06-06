<?php

namespace pms\server\middleware;

use pms\app\inject\http\SafeParamsInject;
use pms\app\Middleware;
use pms\contract\ContainerInterface;
use pms\exception\RequestMethodException;
use pms\exception\RequestParamsException;
use pms\helper\Process;
use pms\server\example\http\SafeParams;

class HttpRequestMiddleware extends Middleware
{

    protected array $config;

    protected array $realParams = [];

    public function handle():void{
        foreach (['method','validate'] as $paramKey){
            $item = $this->class->getProperty($paramKey);
            $this->config[$paramKey] = $item->getDefaultValue();
        }
        $this->method();
        $this->params();
    }

    private function method(): void{
        $config = $this->config['method'] ?? 'get';
        if (gettype($config) === 'string') {
            $config = strtoupper($config);
            $config = str_replace(',', '|', $config);
            $config = explode("|", $config);
            $config = [...$config];
        }
        $current = $this->request->method();
        if (!in_array($current, $config)) {
            throw new RequestMethodException("请求类型不正确");
        }
    }

    private function params(): void{
        $config = $this->config['validate'];
        $paramsDta = $this->request->params();
        $realData = [];
        foreach ($config as $key => $value) {
            $itemConfig = $value;
            $require = $itemConfig['require'] ?? false;
            $datum = $paramsDta[$key] ?? $itemConfig['default'] ?? '';
            if (!empty($datum) || (is_numeric($datum) || is_bool($datum))) {
                if(isset($itemConfig['transform'])){
                    $datum = Process::strToAction($itemConfig['transform'], $datum);
                }
            }
            if ($require && Process::realEmpty($datum)) {
                $this->paramsException($itemConfig['des'] ?? $itemConfig['desc'] ?? $key, $key, 1);
            }else{
                if (!Process::realEmpty($datum)){
                    $type = $itemConfig['type'] ?? '';
                    if(!empty($type) && $type !== 'package' && !Process::validType($type,$datum)){
                        $this->paramsException($itemConfig['des'] ?? $itemConfig['desc'] ?? $key, $key);
                    }
                    $realData[$key] = $datum;
                }
            }
        }
        $this->realParams = $realData;
    }

    private function paramsException($desc,$key,  $type = 2){
        $message = $type === 1 ? "为必填项" : "数据类型不正确";
        throw new RequestParamsException($desc . $message, $type, $key, $desc);
    }

    public function callback(ContainerInterface &$server): void{
        $server->put(SafeParamsInject::class,new SafeParams($this->realParams));
    }
}