<?php
declare(strict_types=1);

namespace pms;

use pms\annotate\Inject;
use pms\contract\ApplicationActionInterface;
use pms\inject\Request;
use pms\inject\SafeParams;

abstract class Controller implements ApplicationActionInterface
{

    protected string|array $method = "GET";
    protected string $contentType = JSON_CONTENT_TYPE;
    protected string $desc = "";
    protected string $comment = '';
    protected array $validate = [];
    protected string $login = LOGIN_TRUE;
    protected array|string $terminal = [];
    /**
     * @var array|string 应用中间件
     */
    protected array|string $middleware = [];

    protected mixed $_return = null;

    #[Inject(SafeParams::class)]
    protected SafeParams $safeParams;

    #[Inject(Request::class)]
    protected Request $request;

    protected bool $token = false;

    public function entry()
    {
        return null;
    }

    public function file(mixed $data): void
    {
        $this->_return = $data;
    }


    public function success(mixed $data, $code = 200, array $other = []): void
    {
        $this->_return = [
            'data' => $data,
            'code' => $code,
        ];
        if(!empty($other)){
            $this->_return['other'] = $other;
        }
    }

    public function error(mixed $message, $code = 500, array $other = []): void
    {
        $this->_return = [
            'message' => $message,
            'code' => $code,
        ];
        if(!empty($other)){
            $this->_return['other'] = $other;
        }
    }


}