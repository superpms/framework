<?php
declare(strict_types=1);

namespace pms\app;

use pms\contract\AppInterface;

abstract class Http implements AppInterface
{
    /**
     * @var string|array|int 接口请求类型
     */
    protected string|array|int $method = METHOD_GET;

    /**
     * @var string 响应数据类型
     */
    protected string $contentType = JSON_CONTENT_TYPE;

    /**
     * @var string 接口名称
     */
    protected string $desc = "";

    /**
     * @var string 接口描述
     */
    protected string $comment = '';

    /**
     * @var array 接口参数验证规则
     */
    protected array $validate = [];

    /**
     * @var array|string 应用中间件
     */
    protected array|string $middleware = [];

    /**
     * 响应数据载体
     * @var mixed|null
     */
    protected mixed $responseData = null;

}