<?php

namespace pms\helper\hrb;

abstract class HRBRequest implements HRBRequestInterface
{

    protected string $uri;
    protected string $method = 'GET';
    protected array $headers = [];
    protected mixed $arguments = [];

    protected array $query = [];

    public function getMethod(): string
    {
        return strtoupper($this->method);
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function pushHeader(string $header): self
    {
        $this->headers [] = $header;
        return $this;
    }

    public function getArguments(): mixed
    {
        return $this->arguments;
    }

    public function setArguments(mixed $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }


    public function getQuery(): array{
        return $this->query;
    }

    public function setQuery(array $query): self{
        $this->query = $query;
        return $this;
    }

    public function callback(mixed $result): mixed
    {
        return $result;
    }

}