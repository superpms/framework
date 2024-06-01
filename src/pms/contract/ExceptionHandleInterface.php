<?php

namespace pms\contract;

interface ExceptionHandleInterface
{
    public function __construct(\Throwable $exception);
    public function handle(\Throwable $exception);
    public function getContentType(): string;
    public function getContent(): mixed;

}