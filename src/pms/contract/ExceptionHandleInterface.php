<?php

namespace pms\contract;

interface ExceptionHandleInterface
{
    public function __construct(\Throwable $exception,\Closure $statusCode);
    public function handle(\Throwable $exception,\Closure $statusCode);
    public function getContent(): mixed;

}