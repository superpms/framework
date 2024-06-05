<?php

namespace pms\server\middleware;

use pms\app\inject\http\SafeParamsInject;
use pms\app\Middleware;
use pms\contract\ContainerInterface;
use pms\exception\AuthException;
use pms\exception\MethodException;
use pms\exception\ParamsException;
use pms\exception\SystemException;
use pms\helper\Hash;
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
            throw new MethodException("请求类型不正确");
        }
    }

    private function auth(): void{
        $configLogin = strtoupper($this->config['login']);
        $configTerminal = $this->config['terminal'] ?? [];
        $userinfo = $this->request->userinfo();
        $terminal = $this->request->terminal();
        if(!is_array($configTerminal)){
            $configTerminal = str_replace('|',',',$configTerminal);
            $configTerminal = explode(",",$configTerminal);
        }
        if(!empty($configTerminal) && !in_array($terminal,$configTerminal)){
            $this->authException("暂无权限");
        }
        if(($configLogin === 'TRUE') && empty($userinfo)){
            $this->authException("未登录");
        }
    }

    private function authException(string $message){
        throw new AuthException($message);
    }

    private function systemException(string $message){
        throw new SystemException($message);
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

    public function token(): void{
        $rTime = $this->request->requestTime();
        $rToken = $this->request->requestToken();
        $privateKey = config('auth.request-privet-key','');
        $effectTime = config('auth.request-token-time',0);
        if(empty($rTime) || empty($rToken)){
            $this->authException("请求验证失败");
        }else if($privateKey === ''){
            $this->systemException("未设置请求签名密钥,无法开启请求签名验证");
        }else if($effectTime === 0){
            $this->systemException("未设置请求签名有效时间,无法开启请求签名验证");
        }else{
            $realToken = Hash::md5("$rTime|pms-framework|$privateKey",$privateKey);
            if($rToken !== $realToken || (time() - $rTime > $effectTime)){
                $this->authException("请求验证失败");
            }
        }
    }

    private function paramsException($desc,$key,  $type = 2){
        $message = $type === 1 ? "为必填项" : "数据类型不正确";
        throw new ParamsException($desc . $message, $type, $key, $desc);
    }

    public function callback(ContainerInterface &$server): void{
        $server->put(SafeParamsInject::class,new SafeParams($this->realParams));
    }
}