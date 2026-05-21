# Boot Lifecycle

`pms\Boot` turns a project root and its `boot.json` into an initialized runtime. It also acts as the dispatch surface for interpreters.

## Constructor Flow

`Boot::__construct(string $rootPath = "")` performs this sequence:

1. Resolve the project root.
2. Register PHP error and exception handlers.
3. Read and validate `boot.json`.
4. Initialize `BootOptions`.
5. Run `LifecycleHook::run(LIFECYCLE_BOOT, $rootPath)`.
6. Store exceptions in `Ctx` through `pms_error_set()`.

The default `LifecycleHook` container already has one boot handler:

```php
LIFECYCLE_BOOT => [
    [\pms\program\boot\Setup::class, 'entry'],
]
```

## Setup Flow

`pms\program\boot\Setup::entry($rootPath)` runs the framework setup chain:

1. `initPath()`
2. `initPhpIni()`
3. `validatePhpExtension()`
4. `validatePhpFunction()`
5. `initAutoload()`
6. `Config::fetchConfig(Path::getConfig())`
7. `initInject()`
8. `initService()`

## Path Initialization

`initPath()` mounts:

- `root`
- `app`
- `config`
- `runtime`

It also ensures runtime subdirectories exist:

- `runtime/app`
- `runtime/base`
- `runtime/interpreter`
- `runtime/interpreter/pid`
- `runtime/interpreter/log`

## Interpreter Dispatch

After construction, property access on `Boot` dispatches an interpreter by name:

```php
(new Boot($rootPath))->http;
(new Boot($rootPath))->terminal;
```

`Boot::__get($name)` calls `InterpreterHook::run($name)`. If no interpreter has been mounted under that name, `InterpreterHook` exits with `解释器 [name] 未安装`.

The interpreter names are not hard-coded in `basic`; they come from adjacent packages.

## Error Finalization

`Boot::__get()` and `Boot::__destruct()` both call `handleError()`. If `pms_error()` still returns an exception after broadcasts/listeners had a chance to handle it, `Boot` clears the stored error and rethrows it.
