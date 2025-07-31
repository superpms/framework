<?php

namespace pms\helper\fetch;

class Response{


    protected array $headers = [];
    protected array $body = [];
    protected array $cookie = [];


    public function __construct(
        /**
         * @var mixed $raw 响应原始数据
         */
        protected mixed $raw,
        /**
         * @var \CurlHandle $handler 请求连接句柄
         */
        protected \CurlHandle $handler,
        /**
         * @var array 请求前置的选项
         */
        protected array $options,
    ){

        if (isset($this->options[CURLOPT_HEADER]) && $this->options[CURLOPT_HEADER]) {
            list($header, $body, $cookie) = $this->formatRaw($this->raw);
            $this->handler = $header;
            $this->cookie = $cookie;
            $this->body = $body;
        } else {
            $this->body = $this->raw;
        }

    }

    /**
     * 获取CURL信息
     * @param int $opt CURLINFO_EFFECTIVE_URL - 最后一个有效的 301 URL地址
     *                 CURLINFO_HTTP_CODE - 最后一个收到的HTTP代码
     *                 CURLINFO_FILETIME - 远程获取文档的时间，如果无法获取，则返回值为“-1”
     *                 CURLINFO_TOTAL_TIME - 最后一次传输所消耗的时间
     *                 CURLINFO_NAMELOOKUP_TIME - 名称解析所消耗的时间
     *                 CURLINFO_CONNECT_TIME - 建立连接所消耗的时间
     *                 CURLINFO_PRETRANSFER_TIME - 从建立连接到准备传输所使用的时间
     *                 CURLINFO_STARTTRANSFER_TIME - 从建立连接到传输开始所使用的时间
     *                 CURLINFO_REDIRECT_TIME - 在事务传输开始前重定向所使用的时间
     *                 CURLINFO_SIZE_UPLOAD - 以字节为单位返回上传数据量的总值
     *                 CURLINFO_SIZE_DOWNLOAD - 以字节为单位返回下载数据量的总值
     *                 CURLINFO_SPEED_DOWNLOAD - 平均下载速度
     *                 CURLINFO_SPEED_UPLOAD - 平均上传速度
     *                 CURLINFO_HEADER_SIZE - header部分的大小
     *                 CURLINFO_HEADER_OUT - 发送请求的字符串
     *                 CURLINFO_REQUEST_SIZE - 在HTTP请求中有问题的请求的大小
     *                 CURLINFO_SSL_VERIFYRESULT - 通过设置CURLOPT_SSL_VERIFYPEER返回的SSL证书验证请求的结果
     *                 CURLINFO_CONTENT_LENGTH_DOWNLOAD - 从Content-Length: field中读取的下载内容长度
     *                 CURLINFO_CONTENT_LENGTH_UPLOAD - 上传内容大小的说明
     *                 CURLINFO_CONTENT_TYPE - 下载内容的Content-Type:值，NULL表示服务器没有发送有效的Content-Type: header
     * @return mixed
     */
    public function getInfo(int $opt): mixed{
        return curl_getinfo($this->handler, $opt);
    }

    public function getRaw()
    {
        return $this->raw;
    }


    public function getCookie()
    {
        return $this->cookie;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getJsonBody(bool $toArray = false){
        if (!empty($this->body) && json_validate($this->body)) {
            $this->body = json_decode($this->body, $toArray);
        }
        return null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }


    protected function formatRaw(string $message): array{
        $result_arr = explode("\r\n\r\n", $message);
        $header = $result_arr[count($result_arr) - 2];
        $body = $result_arr[count($result_arr) - 1];
        if (count($result_arr) > 2) {
            $header = $message;
        }
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $cookie = $matches[1];
        $header_str = explode("\r\n", $header);
        $header_array = [];
        foreach ($header_str as $value) {
            if (!str_contains($value, 'HTTP/')) {
                $temp = explode(": ", $value);
                if (count($temp) === 2) {
                    $header_array[$temp[0]] = $temp[1];
                }
            }
        }
        $header = json_encode($header_array);
        return [$header, $body, $cookie];
    }

}