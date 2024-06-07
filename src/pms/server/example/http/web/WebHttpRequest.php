<?php

namespace pms\server\example\http\web;

use pms\server\example\http\HttpRequest;

class WebHttpRequest extends HttpRequest {
    public function __construct(){
        $this->server = $_SERVER;
        $this->header = $this->getAllHeaders();
        parent::__construct();
    }

    public function init(): void{
        ksort($this->server);
        $this->cookie = $_COOKIE;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->input = file_get_contents("php://input");
        parent::init();
    }


    private function getAllHeaders(): array{
        $headers = [];
        if (function_exists('getallheaders') && getallheaders() !== false) {
            foreach (getallheaders() as $key => $value) {
                $headerName = str_replace(' ', '-', str_replace('_', ' ', strtolower($key)));
                $headers[$headerName] = $value;
            }
        } else {
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    $headerName = str_replace(' ', '-', str_replace('_', ' ', strtolower(substr($key, 5))));
                    $headers[$headerName] = $value;
                }
            }
        }
        ksort($headers);
        return $headers;
    }
}