<?php

namespace pms\server\example\http\swoole;

use pms\server\example\http\HttpRequest;
use Swoole\Http\Request;

class SwooleHttpRequest extends HttpRequest {
    protected Request $request;

    public function __construct(Request $request){
        $this->request = $request;
        $this->server = $request->server;
        parent::__construct();
    }

    public function init(): void{
        $this->header = $this->request->header;
        ksort($this->server);
        $this->cookie = $this->request->cookie ?? [];
        $this->get = $this->request->get ?? [];
        $this->post = $this->request->post ?? [];
        $this->files = $this->request->files ?? [];
        $this->input = $this->request->getContent() ?? "";
        parent::init();
    }

}