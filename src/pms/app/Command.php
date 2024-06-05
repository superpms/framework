<?php

namespace pms\app;

use pms\contract\AppInterface;

abstract class Command implements AppInterface
{

    /**
     * @var string 命令名称
     */
    protected string $name = "";

    /**
     * 命令描述
     * @var string
     */
    protected string $desc = "";

    /**
     * @var array 参数规则验证
     */
    protected array $validate = [];

    public function __construct(protected array $commands){}

}