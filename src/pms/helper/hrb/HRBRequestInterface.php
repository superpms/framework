<?php

namespace pms\helper\hrb;

interface HRBRequestInterface
{
    public function getMethod(): string;

    public function getUri(): string;

    public function getHeaders(): array;
    public function setHeaders(array $headers): self;
    public function pushHeader(string $header): self;

    public function getArguments(): mixed;
    public function setArguments(mixed $arguments): self;

    public function getQuery(): mixed;
    public function setQuery(array $query):self;

    public function callback(mixed $result):mixed;
}