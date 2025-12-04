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

class ErrorException extends RuntimeException
{

    public function __construct($errno, $errStr, $errFile, $errLine, ?Throwable $previous = null){
        $this->message = $errStr;
        $this->code = $errno;
        $this->file = $errFile;
        $this->line = $errLine;
        parent::__construct($this->message, $this->code,$previous);
    }

}
