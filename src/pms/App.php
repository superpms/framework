<?php

namespace pms;

use pms\server\Command;
use pms\server\HttpSwoole;
use pms\server\HttpWeb;
use pms\server\NewsletterSwoole;

/**
 * @property HttpWeb $httpWeb
 * @property HttpSwoole $httpSwoole
 * @property NewsletterSwoole $newsletterSwoole
 * @property Command $command
 */
class App
{

    public function __construct($rootPath = ""){
        new Init($rootPath);
    }


    protected array $server = [
        'httpWeb' => HttpWeb::class,
        'httpSwoole' => HttpSwoole::class,
        'newsletterSwoole' => NewsletterSwoole::class,
        'command' => Command::class
    ];

    public function __get(string $name)
    {
        if (!isset($this->server[$name])) {
            exit("服务不存在");
        }
        $this->server[$name]::run();
    }

}