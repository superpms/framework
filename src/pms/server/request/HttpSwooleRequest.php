<?php

namespace pms\server\request;

use Swoole\Http\Request;

class HttpSwooleRequest extends Common {

    public function __construct(Request $request){
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