<?php

namespace pms\server;

use pms\contract\ServerInterface;
use pms\facade\Db;
use pms\facade\Path;
use pms\facade\RDb;
use pms\server\example\command\CommandOutput;
use pms\server\example\http\swoole\SwooleHttpRequest;
use pms\server\example\http\swoole\SwooleHttpResponse;
use pms\server\example\http\swoole\Example;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper as dumper;

class HttpSwoole implements ServerInterface
{

    protected string $name = 'http-swoole server';

    public static function run()
    {
        Db::isPool(true);
        RDb::isPool(true);
        $host = config('server.http.host', '127.0.0.1');
        $port = config('server.http.port', 9501);
        $setConfig = config('server.http.config', []);
        if (!is_array($setConfig)) {
            $setConfig = [];
        }
        $http = new Server($host, $port);
        $http->set([
            'log_file' => Path::getRuntime('/log/server/swoole.http.log'),
            ...$setConfig,
            'reload_async'=>true,
        ]);
        $output = new CommandOutput();
        $http->on('start', function (Server $server) use ($output, $host, $port) {
            $output->writeArrayBlock([
                $output->setBoldStr($output->setColorStr(TERMINAL_COLOR_GREEN, "● PHP Swoole-Http 服务器")),
                '服务IP: ' . $host,
                '服务端口: ' . $port,
                sprintf('本机访问地址: <http://127.0.0.1:%s/>', $port),
                "\033[31m使用\033[1m`CTRL-C`\033[22m即可退出服务\033[0m",
            ]);
        });
        $http->on('request', function (Request $request, Response $response) {
            self::customShutDownHandler($response);
            self::initVarDumper($response);
            $myRequest = new SwooleHttpRequest($request);
            $myResponse = new SwooleHttpResponse($response);
            $exp = new Example($myRequest, $myResponse);
            $exp->run();
        });
        $http->start();
    }

    public static function customShutDownHandler($response): void{
        register_shutdown_function(function ()use($response) {
            $error = error_get_last();
            if (!empty($error)) {
                swoole_clear_error();
                $response->status(500, 'Server Error');
                if (config('app.debug')) {
                    $response->header("content-type", JSON_CONTENT_TYPE);
                    $response->end(json_encode($error));
                } else {
                    $response->end();
                }
            }
        });
    }

    public static function initVarDumper($response): void{
        if (isRunningInConsole()){
            dumper::setHandler(function ($var, $label = null) use ($response) {
                $cloner = new VarCloner();
                $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
                $dumper = new HtmlDumper();
                $response->header('Content-Type', "text/html");
                $var = $cloner->cloneVar($var);
                if (null !== $label) {
                    $var = $var->withContext(['label' => $label]);
                }
                ob_start();
                $dumper->dump($var);
                $output = ob_get_clean();
                $response->write($output);
            });
        }

    }

}