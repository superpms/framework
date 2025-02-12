<?php
declare(strict_types=1);

namespace pms\app;

use pms\app\inject\http\RequestInject;
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
     * @var string 接口描述
     */
    protected string $desc = "";

    /**
     * @var string 接口描述
     */
    protected string $comment = '';

    /**
     * @var array 接口参数验证规则
     */
    protected array $validate;

    /**
     * @var array|string 接口中间件
     */
    protected array|string $middleware = [];

    /**
     * 响应数据载体
     * @var mixed|null
     */
    protected mixed $responseData = null;

    /**
     * @var string 当前应用名称
     */
    public string $app;

    /**
     * 动态接口参数验证规则-方法
     * @param RequestInject|null $request
     * @return array
     */
    protected static function validate(RequestInject|null $request = null): array
    {
        return [];
    }

    /**
     * entry的前置执行方法，且在当前接口实例化完成之后
     * @return void
     */
    public function __prepare(): void{}


    /**
     * entry的后置执行方法
     * @return void
     */
    public function __teardown(): void{}

}