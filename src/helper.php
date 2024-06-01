<?php
const JSON_CONTENT_TYPE = 'application/json';
const JSONP_CONTENT_TYPE = 'application/javascript';
const ZIP_CONTENT_TYPE = 'application/zip';
const PDF_CONTENT_TYPE = 'application/pdf';

const PLAIN_CONTENT_TYPE = 'text/plain';
const HTML_CONTENT_TYPE = 'text/htm';
const CSS_CONTENT_TYPE = 'text/css';
const JAVASCRIPT_CONTENT_TYPE = 'text/javascript';
const XML_CONTENT_TYPE = 'text/xml';

const PNG_CONTENT_TYPE = 'image/png';
const JPEG_CONTENT_TYPE = 'image/jpeg';
const MPEG_CONTENT_TYPE = 'audio/mpeg';

const LOGIN_TRUE = 'true';
const LOGIN_FALSE = 'false';
const LOGIN_OR = 'or';

if (!function_exists('config')) {
    function config(string $name = null, $default = null)
    {
        // 无参数时获取所有
        if ($name === null) {
            return __CONFIG__;
        }
        $name = explode('.', $name);
        $config = __CONFIG__;
        foreach ($name as $item) {
            if (isset($config[$item])) {
                $config = $config[$item];
            } else {
                return $default;
            }
        }
        return $config;
    }
}

function customErrorHandler($errno, $errstr, $errfile, int $errline){
    throw new \pms\exception\WarningException($errno, $errstr, $errfile, $errline);
}
function customShutDownHandler($response): void
{
    $error = error_get_last();
    if(!empty($error)){
        if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
            ob_end_clean();
        }else{
            swoole_clear_error();
        }
        $response->status(500,'Server Error');
        if(config('app.debug')){
            $response->header("content-type",JSON_CONTENT_TYPE);
            $response->end(json_encode($error));
        }else{
            $response->end();
        }
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars):void
    {
        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) && !headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        if (array_key_exists(0, $vars) && 1 === count($vars)) {
            \Symfony\Component\VarDumper\VarDumper::dump($vars[0]);
        } else {
            foreach ($vars as $k => $v) {
                \Symfony\Component\VarDumper\VarDumper::dump($v, is_int($k) ? 1 + $k : $k);
            }
        }
        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
            exit();
        }else{
            throw new \pms\exception\CliModeForcedInterruptException('');
        }
    }
}