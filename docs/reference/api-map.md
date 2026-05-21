# API Map

This map lists the package-level public surfaces developers usually touch.

## Boot

- `pms\Boot::__construct(string $rootPath = "")`
- `pms\Boot::__get(string $name)`
- `pms\Boot::handleError(): void`

## Facades

- `pms\facade\BootOptions`
- `pms\facade\Config`
- `pms\facade\Ctx`
- `pms\facade\Path`
- `pms\facade\Service`

## Drivers

- `pms\program\boot\Driver::init($boot)`
- `pms\program\boot\Driver::get_extend(string $extend, mixed $default = null)`
- `pms\program\config\Driver::mount(string $key, mixed $data)`
- `pms\program\config\Driver::merge(array $data, bool $inCoroutine = false)`
- `pms\program\config\Driver::set(string $key, string $name, mixed $data)`
- `pms\program\config\Driver::get(?string $name = null, mixed $default = null)`
- `pms\program\config\Driver::fetchConfig(string|array $paths, bool $inCoroutine = false)`
- `pms\program\ctx\Driver::set(string $key, mixed $value)`
- `pms\program\ctx\Driver::get(string $key = '', mixed $default = null)`
- `pms\program\ctx\Driver::has(string $key)`
- `pms\program\path\Driver::init(array $data)`
- `pms\program\path\Driver::mount(string $name, string $path)`
- `pms\program\service\Driver::register(string $serviceAppClass, string $name = '')`
- `pms\program\service\Driver::activeName(string $serviceName)`
- `pms\program\service\Driver::init(array $array)`
- `pms\program\service\Driver::audit()`

## Hooks

- `pms\hook\LifecycleHook::mount($lifecycles, $callable)`
- `pms\hook\LifecycleHook::run(string $lifecycle, ...$args)`
- `pms\hook\LifecycleHook::audit()`
- `pms\hook\InterpreterHook::mount(string $interpreterName, string $interpreterClass)`
- `pms\hook\InterpreterHook::run(string $interpreterName)`
- `pms\hook\InterpreterHook::audit()`
- `pms\hook\AutoloadHook::mount(string|array|\Closure $filePaths)`
- `pms\hook\AutoloadHook::run()`
- `pms\hook\AutoloadHook::audit()`
- `pms\hook\AnnotationClassHook::mount(string $annotateClass, \Closure $fn)`
- `pms\hook\AnnotationClassHook::run(\ReflectionClass &$class, pms\Container &$server)`
- `pms\hook\AnnotationPropertyHook::mount(string $annotateClass, \Closure $fn)`
- `pms\hook\AnnotationPropertyHook::run(\ReflectionClass $class, object &$obj, pms\Container &$server)`

## Base Apps

- `pms\app\InterpreterApp`
- `pms\app\LifecycleHookApp`
- `pms\app\AdapterApp`
- `pms\app\ServiceApp`
- `pms\app\BroadcastApp`

## Container

- `pms\Container::invokeClass(string|\ReflectionClass $class, $args = [])`
- `pms\Container::get($name, $args = [])`
- `pms\Container::has($name)`
- `pms\Container::make($name, $args = [])`
- `pms\Container::put(string $name, $class)`

## Attribute

- `#[pms\annotate\Inject(string $classname, ...$args)]`

## Broadcast

- `pms\broadcast\SystemErrorBroadcast::listener(callable|array $closure, bool|array $immediate = false)`
- `pms\broadcast\SystemErrorBroadcast::trigger(...$args)`
- `pms\broadcast\SystemErrorBroadcast::audit()`
