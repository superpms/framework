<?php

namespace pms\server;

use pms\Container;
use pms\contract\ApplicationActionInterface;
use pms\contract\ExceptionHandleInterface;
use pms\exception\ClassNotFoundException;
use pms\exception\CliModeForcedInterruptException;
use pms\facade\Db;
use pms\facade\RDb;
use pms\inject\Request;
use pms\inject\Response;
use ReflectionClass;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper as dumper;

abstract class Common extends Container
{
    protected bool $connectionPool = false;
    protected Request $request;
    protected Response $response;
    protected string $app = '';
    protected array $middlewares = [];

    protected function initDatabase(): void
    {
        Db::setConfig(config('database'));
        RDb::setConfig(config('redis'));
        if ($this->connectionPool) {
            Db::isPool(true);
            RDb::isPool(true);
        }
    }

    protected function middleware(string $classNamespace): ReflectionClass|null
    {
        return null;
    }

    protected function initVarDumper(): void
    {
        if (php_sapi_name() === 'cli') {
            $dumper = new HtmlDumper();
            $cloner = new VarCloner();
            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
            dumper::setHandler(function ($var, $label = null) use ($dumper, $cloner) {
                $var = $cloner->cloneVar($var);
                if (null !== $label) {
                    $var = $var->withContext(['label' => $label]);
                }
                ob_start();
                $dumper->dump($var);
                $output = ob_get_clean();
                $this->response->setStatusCode(500, 'Internal Server Error');
                $this->response->write($output);
            });
        }
    }

    protected function execute(string $pathinfo,\Closure $callback = null){
        register_shutdown_function('customShutDownHandler',$this->response);
        $this->initVarDumper();
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
        $namespace = str_replace(DIRECTORY_SEPARATOR, '\\', $pathinfo);
        if (!class_exists($namespace)) {
            throw new ClassNotFoundException($namespace);
        }
        $this->initDatabase();
        $this->initMiddlewareConfig();

        $class = $this->middleware($namespace);
        if ($class !== null) {
            $obj = $this->invokeClass($class);
        } else {
            $obj = $this->invokeClass($namespace);
        }
        /**
         * @var $obj ApplicationActionInterface
         */
        $data = $obj->entry();
        if ($data === null) {
            $data = $class->getProperty('_return')->getValue($obj);
        }
        if($callback !== null){
            $callback($class,$obj);
        }
        return $data;
    }

    /**
     * 加载异常处理器
     * @param \Throwable $e
     * @param bool $inUser 是否使用应用内客制化处理器
     * @return void
     */
    protected function exceptionHandle(\Throwable $e, bool $inUser = true): void{
        try{
            if (!($e instanceof CliModeForcedInterruptException)) {
                $userHandle = "\\app\\$this->app\\ExceptionHandle";
                $systemHandle = "\\pms\\ExceptionHandle";
                $handle = $systemHandle;
                if($inUser && class_exists($userHandle)){
                    $handle = $userHandle;
                }
                $class = $this->getClass($handle);
                /**
                 * @var ExceptionHandleInterface $obj
                 */
                $obj = $this->invokeClass($class,[
                    'exception' => $e
                ]);
                $data = $this->contentToString($obj->getContent(), $obj->getContentType());
//                $this->response->header('Content-Type', $obj->getContentType());
                $this->response->end($data);
            } else {
                $this->response->end('');
            }
        }catch (\Throwable $e){
            // 如果客制化Handle异常，则抛出系统的异常
            $this->exceptionHandle($e,false);
        }

    }

    protected function initMiddlewareConfig(): void
    {
        $config = [];
        $middlewarePath = join(DIRECTORY_SEPARATOR, [
            __APP_PATH__,
            $this->app,
            'middleware.php'
        ]);
        if(file_exists($middlewarePath)){
            $middleware = include $middlewarePath;
            if(is_array($middleware)){
                $config = $middleware;
            }
        }
        $this->middlewares = [
            ...$this->middlewares,
            ...$config,
        ];
    }

}