<?php

namespace pms\server\middleware;

use pms\app\inject\http\SafeParamsInject;
use pms\app\Middleware;
use pms\contract\ContainerInterface;
use pms\exception\RequestMethodException;
use pms\exception\RequestParamsException;
use pms\helper\Data;
use pms\helper\Process;
use pms\server\example\http\SafeParams;

class HttpRequestMiddleware extends Middleware
{

    protected array $config;

    protected array $realParams = [];
    protected array $bitOr = [
        METHOD_GET => 'GET',
        METHOD_POST => "POST",
        METHOD_PUT => "PUT",
        METHOD_DELETE => "DELETE",
        METHOD_PATCH => "PATCH",
        METHOD_HEAD => "HEAD",
    ];

    public function handle(): void
    {
        foreach (['method', 'validate'] as $paramKey) {
            $item = $this->class->getProperty($paramKey);
            $this->config[$paramKey] = $item->getDefaultValue();
        }
        $this->method();
        $this->params();
    }

    private function method(): void
    {
        $config = $this->config['method'] ?? 'GET';
        if (is_string($config)) {
            $config = strtoupper($config);
            $config = str_replace(',', '|', $config);
            $config = explode("|", $config);
            $config = [...$config];
        } else if (is_int($config)) {
            $config = Data::separateBitOr([
                METHOD_GET,
                METHOD_POST,
                METHOD_PUT,
                METHOD_DELETE,
                METHOD_PATCH,
                METHOD_HEAD,
            ], $config);
            $config = array_map(function ($v) {
                return $this->bitOr[$v];
            }, $config);
        } else if (is_array($config)) {
            $config = array_map(function ($v) {
                if(is_int($v)){
                    return $this->bitOr[$v] ?? array_map(function ($v) {
                        return $this->bitOr[$v];
                    },Data::separateBitOr([
                        METHOD_GET,
                        METHOD_POST,
                        METHOD_PUT,
                        METHOD_DELETE,
                        METHOD_PATCH,
                        METHOD_HEAD,
                    ], $v));
                }else{
                    return strtoupper($v);
                }
            }, $config);
        }
        $current = $this->request->method();
        $real = [];
        foreach ($config as $v){
            if(is_array($v)){
                $real = [...$real,...$v];
            }else{
                $real[] = $v;
            }
        }
        if (!in_array($current, $config)) {
            throw new RequestMethodException("请求类型不正确");
        }
    }

    private function params(): void
    {
        $config = $this->config['validate'];
        $paramsDta = $this->request->params();
        $realData = [];
        foreach ($config as $key => $value) {
            $itemConfig = $value;
            $require = $itemConfig['require'] ?? false;
            $datum = $paramsDta[$key] ?? $itemConfig['default'] ?? '';
            if (!empty($datum) || (is_numeric($datum) || is_bool($datum))) {
                if (isset($itemConfig['transform'])) {
                    $datum = Process::strToAction($itemConfig['transform'], $datum);
                }
            }
            if ($require && Process::realEmpty($datum)) {
                $this->paramsException($itemConfig['des'] ?? $itemConfig['desc'] ?? $key, $key, 1);
            } else {
                if (!Process::realEmpty($datum)) {
                    $type = $itemConfig['type'] ?? '';
                    if (!empty($type) && $type !== 'package' && !Process::validType($type, $datum)) {
                        $this->paramsException($itemConfig['des'] ?? $itemConfig['desc'] ?? $key, $key);
                    }
                    $realData[$key] = $datum;
                }
            }
        }
        $this->realParams = $realData;
    }

    private function paramsException($desc, $key, $type = 2)
    {
        $message = $type === 1 ? "为必填项" : "数据类型不正确";
        throw new RequestParamsException($desc . $message, $type, $key, $desc);
    }

    public function callback(ContainerInterface &$server): void
    {
        $server->put(SafeParamsInject::class, new SafeParams($this->realParams));
    }
}