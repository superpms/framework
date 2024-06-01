<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace pms\exception;

use RuntimeException;
use Throwable;

class ClassNotFoundException extends RuntimeException
{
    public function __construct(protected string $class = '', Throwable $previous = null)
    {
        parent::__construct('class not exists: '.$class, 0, $previous);
    }

    /**
     * 获取类名
     * @access public
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }
}
