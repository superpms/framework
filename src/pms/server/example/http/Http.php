<?php

namespace pms\server\example\http;

use pms\app\inject\http\RequestInject;
use pms\app\inject\http\ResponseInject;
use pms\contract\AppInterface;
use pms\contract\ExceptionHandleInterface;
use pms\contract\MiddlewareInterface;
use pms\exception\ClassNotFoundException;
use pms\exception\CliModeForcedInterruptException;
use pms\exception\SystemException;
use pms\facade\Path;
use pms\helper\Data;
use pms\server\example\Server;
use pms\server\middleware\HttpRequestMiddleware;
use ReflectionClass;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper as dumper;

abstract class Http extends Server
{
    protected RequestInject $request;
    protected ResponseInject $response;
    protected array $middlewares = [
        HttpRequestMiddleware::class
    ];
    protected string $contentType = JSON_CONTENT_TYPE;

    public function __construct(RequestInject $request, ResponseInject $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function run(): void
    {
        try {
            set_error_handler('customErrorHandler');
            $isIn = $this->inHttpApp();
            if (!$isIn) {
                $this->sendFile($this->request->pathinfo());
                return;
            }
            $this->initAppConfig();
            $this->initCors();
            $isOptions = $this->isOptionsMethod();
            if ($isOptions) {
                return;
            }
            $this->putInject();
            $this->customShutDownHandler();
            $this->initVarDumper();
            $data = $this->execute($this->getRealPathInfo(), function (ReflectionClass $class, AppInterface $obj) {
                $contentType = $class->getProperty('contentType');
                $this->contentType = $contentType->getValue($obj);
            });
            if ($this->response->isWritable()) {
                $data = $this->contentToString($data, $this->contentType);
                $this->response->header('Content-Type', $this->contentType);
                $this->response->end($data);
            }
        } catch (\Throwable $e) {
            $this->response->header('Content-Type', $this->contentType);
            $this->exceptionHandle($e);
        }
    }

    protected function inHttpApp(): bool
    {
        $pathinfo = $this->request->pathinfo();
        $apps = config('app.apps.http');
        if (is_string($apps)) {
            $apps = [$apps];
        }
        $inApp = false;
        foreach ($apps as $app) {
            if (str_starts_with($pathinfo, '/' . $app)) {
                $inApp = true;
                $this->app = $app;
                break;
            }
        }
        return $inApp;
    }

    protected function initCors(): void
    {
        $responseHeader = config('http.cors', []);
        foreach ($responseHeader as $key => $value) {
            $this->response->header($key, $value);
        }
    }

    protected function isOptionsMethod(): bool
    {
        if ($this->request->method() === 'OPTIONS') {
            $this->response->end();
            return true;
        }
        return false;
    }

    protected function sendFile(string $pathinfo): void
    {
        $filePath = Path::getPublic($pathinfo);
        if (is_file($filePath)) {
            $this->response->header('Content-Type', mime_content_type($filePath));
            $this->response->end(file_get_contents($filePath));
        } else {
            $this->response->status(404);
        }
    }

    protected function putInject(): void
    {
        $this->put(RequestInject::class, $this->request);
        $this->put(ResponseInject::class, $this->response);
    }

    protected function getRealPathInfo(): string
    {
        $pathinfo = $this->request->pathinfo();
        $defaultPath = [
            '/' . $this->app,
            '/' . $this->app . "/",
        ];
        if (in_array($pathinfo, $defaultPath)) {
            $defaultController = config('http.default_controller', 'Index');
            $pathinfo = DIRECTORY_SEPARATOR . $this->app . DIRECTORY_SEPARATOR . $defaultController;
        }
        return $pathinfo;
    }


    protected function initMiddlewareConfig(): void
    {
        $config = [];
        $middlewarePath = Path::getApp($this->app . "/middleware.php");
        if (file_exists($middlewarePath)) {
            $middleware = include $middlewarePath;
            if (is_array($middleware)) {
                $config = $middleware;
            }
        }
        $this->middlewares = [
            ...$this->middlewares,
            ...$config,
        ];
    }

    protected function middleware(string $classNamespace, \Closure $callback = null): ReflectionClass
    {
        try {
            $actionClass = $this->getClass($classNamespace);
        } catch (\Throwable $e) {
            throw new ClassNotFoundException($classNamespace, $e);
        }
        $this->contentType = $actionClass->getProperty('contentType')->getDefaultValue();
        // 执行应用全局中间件
        $this->runMiddleware($this->middlewares, [
            'class' => $actionClass,
            'request' => $this->request,
        ]);
        // 执行应用独立中间件
        $actionMiddlewares = $actionClass->getProperty('middleware')->getDefaultValue();
        $this->runMiddleware($actionMiddlewares, [
            'class' => $actionClass,
            'request' => $this->request,
        ]);
        return $actionClass;
    }

    /**
     * 执行中间件
     * @param array|string $middlewares
     * @param array $args
     * @return void
     */
    protected function runMiddleware(array|string $middlewares, array $args): void
    {
        if (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }
        foreach ($middlewares as $item) {
            /**
             * @var $obj MiddlewareInterface
             */
            if (class_exists($item)) {
                $mClass = $this->getClass($item);
                $obj = $this->invokeClass($mClass, $args);
                $obj->handle();
                if ($mClass->hasMethod('callback')) {
                    $obj->callback($this);
                }
            } else {
                throw new SystemException("middleware is not found:" . $item);
            }
        }
    }

    protected function initVarDumper(): void
    {
        if (PHP_SAPI === 'cli') {
            $dumper = new HtmlDumper();
            $cloner = new VarCloner();
            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
            dumper::setHandler(function ($var, $label = null) use ($dumper, $cloner) {
                $this->response->header('Content-Type', "text/html");
                $var = $cloner->cloneVar($var);
                if (null !== $label) {
                    $var = $var->withContext(['label' => $label]);
                }
                ob_start();
                $dumper->dump($var);
                $output = ob_get_clean();
                $this->response->write($output);
            });
        }
    }

    public function customShutDownHandler(): void
    {
        register_shutdown_function(function () {
            $error = error_get_last();
            if (!empty($error)) {
                if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
                    ob_end_clean();
                } else {
                    swoole_clear_error();
                }
                $this->response->status(500, 'Server Error');
                if (config('app.debug')) {
                    $this->response->header("content-type", $this->contentType);
                    $this->response->end(json_encode($error));
                } else {
                    $this->response->end();
                }
            }
        });
    }

    protected function contentToString(mixed $data, string $contentType)
    {
        if (is_string($data)) {
            return $data;
        }
        return match ($contentType) {
            JSON_CONTENT_TYPE => json_encode($data, 320),
            JSONP_CONTENT_TYPE => $this->request->get('callback', 'callback') . '(' . json_encode($data) . ')',
            XML_CONTENT_TYPE => Data::arrayToXml($data),
            default => is_array($data) || is_object($data) ? json_encode($data, 320) : $data,
        };
    }

    protected function execute(string $pathinfo, \Closure $callback = null)
    {

        $namespace = $this->pathinfoToNamespace($pathinfo);

        if (!class_exists($namespace)) {
            throw new ClassNotFoundException($namespace);
        }

        $this->initMiddlewareConfig();
        $class = $this->middleware($namespace, $callback);
        $obj = $this->invokeClass($class);
        /**
         * @var $obj AppInterface
         */
        $data = $obj->entry();
        if ($data === null) {
            $data = $class->getProperty('responseData')->getValue($obj);
        }
        if ($callback !== null) {
            $callback($class, $obj);
        }
        return $data;
    }

    protected function pathinfoToNamespace(string $pathinfo): string
    {
        $packageName = config('app.package_name', 'package');
        $pathinfo = str_replace(".", DIRECTORY_SEPARATOR, $pathinfo);
        $pathinfo = str_replace("\\", DIRECTORY_SEPARATOR, $pathinfo);
        $pathinfo = str_replace("\\\\", DIRECTORY_SEPARATOR, $pathinfo);
        $pathinfo = str_replace("//", DIRECTORY_SEPARATOR, $pathinfo);
        $pathinfo = str_replace("/", DIRECTORY_SEPARATOR, $pathinfo);
        $pathinfo = trim($pathinfo, DIRECTORY_SEPARATOR);
        $pathinfo = explode(DIRECTORY_SEPARATOR, $pathinfo);
        $pathinfo = join(DIRECTORY_SEPARATOR, [
            '',
            'app',
            ...array_slice($pathinfo, 0, 1),
            $packageName,
            ...array_slice($pathinfo, 2, count($pathinfo) - 3),
            ucfirst($pathinfo[count($pathinfo) - 1])
        ]);
        return str_replace(DIRECTORY_SEPARATOR, '\\', $pathinfo);
    }


    /**
     * 加载异常处理器
     * @param \Throwable $e
     * @param bool $inUser 是否使用应用内客制化处理器
     * @return void
     */
    protected function exceptionHandle(\Throwable $e, bool $inUser = true): void
    {
        try {
            if (!($e instanceof CliModeForcedInterruptException)) {
                $userHandle = "\\app\\$this->app\\ExceptionHandle";
                $systemHandle = "\\pms\\ExceptionHandle";
                $handle = $systemHandle;
                if ($inUser && class_exists($userHandle)) {
                    $handle = $userHandle;
                }
                $class = $this->getClass($handle);
                /**
                 * @var ExceptionHandleInterface $obj
                 */
                $obj = $this->invokeClass($class, [
                    'exception' => $e
                ]);
                $data = $this->contentToString($obj->getContent(), $this->contentType);
                $this->response->end($data);
            } else {
                $this->response->end('');
            }
        } catch (\Throwable $e) {
            // 如果客制化Handle异常，则抛出系统的异常
            $this->exceptionHandle($e, false);
        }

    }

}