<?php

namespace pms;

use pms\server\entry\HttpWeb;
use pms\server\entry\HttpSwoole;
/**
 * @property HttpWeb $httpWeb
 * @property HttpSwoole $httpSwoole
 */
class Server extends Container {

    protected array $args = [];
    public function __construct($rootPath = ""){
        $this->args['rootPath'] = $rootPath;
    }

    protected array $bind = [
        'httpWeb'=>HttpWeb::class,
        'httpSwoole'=>HttpSwoole::class,
    ];

}