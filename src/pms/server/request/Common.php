<?php

namespace pms\server\request;

use pms\inject\Request;

abstract class Common implements Request
{
    protected array $server = [];
    protected array $header = [];
    protected bool $isHttps;
    protected string $ip;
    protected string $scheme;
    protected string $host;
    protected string $pathinfo;
    protected array $get = [];
    protected array $post = [];
    protected string  $input = '';
    protected array $files = [];
    protected array $cookie = [];
    protected array $params = [];
    protected string $method;
    protected string $contentType;

    public function server(string $name = null, string $default = null): array|string|null{
        if ($name === null) {
            return $this->server;
        }
        return $this->server[strtoupper($name)]  ?? $this->server[$name] ?? $this->server[strtolower($name)] ?? $default;
    }
    public function header(string $name = null, string $default = null): array|string|null{
        if ($name === null) {
            return $this->header;
        }
        return $this->header[$name] ?? $this->header[strtolower($name)] ?? $this->header[strtoupper($name)] ?? $default;
    }
    public function params(string $name = null, string $default = null): array|string|null{
        if ($name === null) {
            return $this->params;
        }
        return $this->params[$name] ?? $default;
    }
    public function cookie(string $name = null, string $default = null): array|string|null{
        if ($name === null) {
            return $this->cookie;
        }
        return $this->cookie[$name] ?? $default;
    }
    public function files(string $name = null, string $default = null): array|string|null{
        if ($name === null) {
            return $this->files;
        }
        return $this->files[$name] ?? $default;
    }
    public function post(string $name = null, string $default = null): array|string|null{
        if ($name === null) {
            return $this->post;
        }
        return $this->post[$name] ?? $default;
    }

    public function get(string $name = null, string $default = null): array|string|null{
        if ($name === null) {
            return $this->get;
        }
        return $this->get[$name] ?? $default;
    }
    public function input(): string{
        return $this->input;
    }
    public function contentType(): string{
        return $this->contentType;
    }
    public function ip(): string{
        return $this->ip;
    }
    public function scheme(): string{
        return $this->scheme;
    }
    public function host(): string{
        return $this->host;
    }
    public function pathinfo(): string{
        return $this->pathinfo;
    }
    public function isHttps(): bool{
        return $this->isHttps;
    }
    public function isAjax(): bool{
        return strtoupper($this->header('x-requested-with')) === strtoupper('XMLHttpRequest');
    }
    public function isPjax(): bool{
        return strtoupper($this->header('x-pjax')) === 'TRUE';
    }
    public function isPost(): bool{
        return $this->method() === 'POST';
    }

    public function method(): string
    {
        return $this->method;
    }

    public function terminal(): string|null
    {
        return $this->header(config('web.request_header.terminal','x-terminal'));
    }
    public function userinfo(): mixed
    {
        return (int) $this->header(config('web.request_header.userinfo','x-userinfo'));
    }
    public function token(): string|null
    {
        return $this->header(config('web.request_header.token','x-token'));
    }
    public function time(): int|null
    {
        return (int) $this->header(config('web.request_header.time','x-time'));
    }
    public function lastTime(): int|null
    {
        return (int) $this->header(config('web.request_header.last_time','x-last-time'));
    }
    public function requestTime(): int|null
    {
        return (int) $this->header(config('web.request_header.request_time','r-request-time'));
    }
    public function requestToken(): string|null
    {
        return $this->header(config('web.request_header.request_token','r-request-token'));
    }

    protected function getIsHttps(): bool{
        $schemeName = config('web.request_header.scheme_name', 'x-forwarded-scheme');
        if(is_string($schemeName)){
            $schemeName = [$schemeName];
        }
        $isHttps = false;
        foreach ($schemeName as $value){
            if($this->header(strtolower($value)) === 'https'){
                $isHttps = true;
                break;
            }
        }
        if(!$isHttps && strtoupper($this->server('https','off')) === 'ON'){
            $isHttps = true;
        }
        return $isHttps;
    }

    protected function getIp(): string
    {
        $ipName = config('web.request_header.ip_name', 'x-real-ip');
        if(is_string($ipName)){
            $ipName = [$ipName];
        }
        $ip = '';
        foreach ($ipName as $value){
            $t = $this->header(strtolower($value),'');
            if($t !== ''){
                $ip = $t;
                break;
            }
        }
        if($ip === ''){
            $ip = $this->server('remote_addr');
        }
        return $ip;
    }

    protected function init()
    {
        $this->isHttps = $this->getIsHttps();
        $this->ip = $this->getIp();
        $this->host = $this->header('host');
        $this->scheme = $this->isHttps ? "https" : "http";
        $this->method = strtoupper($this->server('request_method'));
        $pathinfo = $this->server('request_uri');
        if(empty($pathinfo)){
            $pathinfo = "/";
        }
        if(strpos($pathinfo,"?") !== false){
            $pathinfo = substr($pathinfo,0,strpos($pathinfo,"?"));
        }
        $this->pathinfo = $pathinfo;

        $this->contentType = $this->header('content-type','text/plain');
        if(strtolower($this->contentType) === 'application/json'){
            $this->input = json_decode($this->input,true);
        }
        $this->params = array_merge($this->get,$this->post,$this->files);
    }
}