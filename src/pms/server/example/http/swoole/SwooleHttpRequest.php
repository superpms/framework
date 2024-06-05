<?php

namespace pms\server\example\http\swoole;

use pms\server\example\http\HttpRequest;
use Swoole\Http\Request as baseRequest;

class SwooleHttpRequest extends HttpRequest {

    public function __construct(baseRequest $request){
        $this->server = $request->server;
        ksort($this->server);
        $this->header = $request->header ?? [];
        $this->cookie = $request->cookie ?? [];
        $this->get = $request->get ?? [];
        $this->post = $request->post ?? [];
        $this->files = $request->files ?? [];
        $this->input = $request->getContent() ?? "";
        $this->init();
    }


}