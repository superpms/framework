<?php

namespace pms\server\request;

class HttpWebRequest extends Common {
    public function __construct(){
        $this->server = $_SERVER;
        ksort($this->server);
        $this->header = $this->getAllHeaders();
        $this->cookie = $_COOKIE;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->input = file_get_contents("php://input");
        $this->init();
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