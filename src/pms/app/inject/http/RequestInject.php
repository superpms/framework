<?php

namespace pms\app\inject\http;

interface RequestInject {

    public function server(string $name = null, mixed $default = null): mixed;

    public function header(string $name = null, mixed $default = null): mixed;

    public function params(string $name = null, mixed $default = null): mixed;

    public function cookie(string $name = null, mixed $default = null): mixed;

    public function files(string $name = null, mixed $default = null): mixed;

    public function post(string $name = null, mixed $default = null): mixed;
    public function get(string $name = null, mixed $default = null): mixed;
    public function input(): string;
    public function contentType(): string;
    public function ip(): string;
    public function scheme(): string;
    public function host(): string;
    public function pathinfo(): string;
    public function isHttps(): bool;
    public function isAjax(): bool;
    public function isPjax(): bool;
    public function isPost(): bool;
    public function isGet(): bool;
    public function isPut(): bool;
    public function isDelete(): bool;
    public function isHead(): bool;
    public function isOptions(): bool;
    public function method(): string;
    public function setAttach(string $key,mixed $data):void;
    public function getAttach(string $key, mixed $default = null):mixed;
}