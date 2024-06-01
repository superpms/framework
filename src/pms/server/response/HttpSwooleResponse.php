<?php

namespace pms\server\response;

use Swoole\Http\Response;
use pms\inject\Response as inf;

/**
 * @mixin Response
 */
class HttpSwooleResponse implements inf {

    private Response $response;
    public function __construct(Response $response){
        $this->response = $response;
    }

    public function __call(string $name, array $arguments){
        return call_user_func_array([$this->response,$name],$arguments);
    }

    public function __get(string $name){
        return $this->response->$name;
    }

    public function isWritable(): bool
    {
        return $this->response->isWritable();
    }

    public function cookie(string $name, string $value = '', int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = '', string $priority = ''): bool
    {
        return $this->response->cookie($name,$value,$expires,$path,$domain,$secure,$httponly,$samesite,$priority);
    }

    public function setCookie(string $name, string $value = '', int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = '', string $priority = ''): bool
    {
        return $this->response->setCookie($name,$value,$expires,$path,$domain,$secure,$httponly,$samesite,$priority);
    }

    public function rawcookie(string $name, string $value = '', int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = '', string $priority = ''): bool
    {
        return $this->response->rawcookie($name,$value,$expires,$path,$domain,$secure,$httponly,$samesite,$priority);
    }

    public function status(int $http_code, string $reason = ''): bool
    {
        return $this->response->status($http_code,$reason);
    }

    public function setStatusCode(int $http_code, string $reason = ''): bool
    {
        return $this->response->setStatusCode($http_code,$reason);
    }

    public function header(string $key, array|string $value, bool $format = true): bool
    {
        return $this->response->header($key,$value,$format);
    }

    public function setHeader(string $key, array|string $value, bool $format = true): bool
    {
        return $this->response->setHeader($key,$value,$format);
    }

    public function trailer(string $key, string $value): bool
    {
        return $this->response->trailer($key,$value);
    }

    public function write(string $content): bool
    {
        return $this->response->write($content);
    }

    public function end(?string $content = null): mixed
    {
        return $this->response->end($content);
    }

    public function sendfile(string $filename, int $offset = 0, int $length = 0): bool
    {
        return $this->response->sendfile($filename,$offset,$length);
    }

    public function redirect(string $location, int $http_code = 302): bool
    {
        return $this->response->redirect($location,$http_code);
    }

    public function detach(): bool
    {
        return $this->response->detach();
    }

    public function create(object|array|int $server = -1, int $fd = -1): self|false
    {
        return $this->response::create($server,$fd);
    }
}