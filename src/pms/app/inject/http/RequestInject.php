<?php

namespace pms\app\inject\http;

interface RequestInject {

    public function server(string $name = null, string $default = null): array|string|null;

    public function header(string $name = null, string $default = null): array|string|null;

    public function params(string $name = null, string $default = null): array|string|null;

    public function cookie(string $name = null, string $default = null): array|string|null;

    public function files(string $name = null, string $default = null): array|string|null;

    public function post(string $name = null, string $default = null): array|string| null;
    public function get(string $name = null, string $default = null): array|string| null;
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
    public function terminal(): string|null;
    public function userinfo(): mixed;
    public function token(): string|null;
    public function time(): int|null;
    public function lastTime(): int|null;
    public function requestTime(): int|null;
    public function requestToken(): string|null;
    public function method(): string;
}