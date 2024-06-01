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

class WarningException extends RuntimeException
{

    public function __construct($errno, $errstr, $errfile, $errline){
        $this->message = $errstr;
        $this->code = $errno;
        $this->file = $errfile;
        $this->line = $errline;
        parent::__construct($this->message, $this->code,null);
    }

}
