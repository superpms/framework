<?php

namespace pms\server;

use pms\inject\Response;
use pms\server\request\HttpWebRequest;
use pms\server\request\SafeParams;

class HttpWeb extends Http {

    protected array $instances = [];
    protected array $bind = [
        'pms\inject\Request' => HttpWebRequest::class,
        'pms\inject\Response' => Response::class,
        'pms\inject\SafeParams' => SafeParams::class,
    ];
}