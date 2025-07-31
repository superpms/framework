<?php

namespace pms\helper\fetch;


class Client
{

    const UA_WIN_CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36';
    const UA_MAC_CHROME = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/605.1.15 (KHTML, like Gecko) MicroMessenger/6.5.2.501';
    const UA_ANDROID_CHROME = 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36';
    const UA_ANDROID_WECHAT = 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Mobile Safari/537.36 MicroMessenger/8.0.5';
    const UA_HARMONY_WECHAT = 'Mozilla/5.0 (Phone; OpenHarmony 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 ArkWeb/4.1.6.1 Mobile HuaweiBrowser/5.0.4.303 MicroMessenger/8.0.5';
    const UA_IOS_CHROME = 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1';
    const UA_IOS_WECHAT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1 MicroMessenger/8.0.5';

    /**
     * CURL 连接句柄
     * @var \CurlHandle|false
     */
    protected \CurlHandle|false $handler;

    protected array $options = array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_FOLLOWLOCATION => 1,
        // 支持gzip
        CURLOPT_ENCODING => '',
        CURLOPT_USERAGENT => self::UA_WIN_CHROME,
        CURLOPT_AUTOREFERER => 1,
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
    );


    public function __construct(){
        $this->handler = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->handler);
    }


    public function addOptions(array|int $option, mixed $value = null): static
    {
        if($value !== null && !is_array($option)){
            $this->options[$option] = $value;
        }else{
            $this->options = array_merge($this->options, [$option=>$value]);
        }
        return $this;
    }

    /**
     * 设置请求地址
     * @param string $uri
     * @return static
     */
    public function setUrl(string $uri): static
    {
        $this->options[CURLOPT_URL] = $uri;
        return $this;
    }

    public function setMethod(string $method){
        $method = strtoupper($method);
        $this->options[CURLOPT_CUSTOMREQUEST] = $method;
    }

    public function setData(mixed $data){
        $method = strtoupper($this->options[CURLOPT_CUSTOMREQUEST]);
        switch ($method) {
            case "GET":
                if (is_array($data) || is_object($data)) {
                    $params = http_build_query($data);
                } else {
                    $params = $data;
                }
                if(!empty($params)){
                    $url = $this->options[CURLOPT_URL];
                    if (!str_contains($url, "?")) {
                        $this->options[CURLOPT_URL] = $url . "?" . $params;
                    } else {
                        $this->options[CURLOPT_URL] = $url . "&" . $params;
                    }
                }
                break;
            case "POST":
                $this->options[CURLOPT_POST] = 1;
                $this->options[CURLOPT_POSTFIELDS] = $data;
                break;
            case "HEAD":
                $this->options[CURLOPT_HEADER] = 1;
                break;
        }
        return $this;
    }

    /**
     * 设置请求头
     * @param array|string $header
     * @param mixed|null $value
     * @return static
     */
    public function addHeader(array|string $header,mixed $value=null): static
    {
        $oldHeader = $this->options[CURLOPT_HTTPHEADER] ?? [];
        if($value === null){
            if(is_array($header)){
                $this->options[CURLOPT_HTTPHEADER] = [
                    ...$oldHeader,
                    $header
                ];
            }
        }else{
            $oldHeader[] = "$header:$value";
            $this->options[CURLOPT_HTTPHEADER] = $oldHeader;
        }
        return $this;
    }

    /**
     * 设置Cookie
     * @param array|string $cookie
     * @param mixed|null $value
     * @return static
     */
    public function addCookie(array|string $cookie,mixed $value=null): static
    {
        $oldCookie = $this->options[CURLOPT_COOKIE] ?? [];
        if($value === null){
            if(is_array($cookie)){
                $this->options[CURLOPT_COOKIE] = [
                    ...$oldCookie,
                    $cookie
                ];
            }
        }else{
            $oldHeader[] = $cookie;
            $this->options[CURLOPT_COOKIE] = $oldHeader;
        }
        return $this;
    }

    /**
     * 设置超时时间
     * @param int $timeout
     * @return static
     */
    public function setTimeout(int $timeout): static
    {
        $this->options[CURLOPT_TIMEOUT] = $timeout;
        return $this;
    }


    /**
     * 设置在HTTP请求头中"Referer: "的内容。
     * @param string $referer
     * @return static
     */
    public function setReferer(string $referer): static
    {
        $this->options[CURLOPT_REFERER] = $referer;
        return $this;
    }

    /**
     * @param string $userAgent
     * @return static
     */
    public function setUserAgent(string $userAgent): static
    {
        $this->options[CURLOPT_USERAGENT] = $userAgent;
        return $this;
    }


    public function execute(): Response
    {
        curl_setopt_array($this->handler, $this->options);
        if (curl_errno($this->handler)) {
            throw new \Exception(curl_error($this->handler), curl_errno($this->handler));
        }
        return new Response(curl_exec($this->handler),$this->handler,$this->options);
    }


}