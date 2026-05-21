# Error Handling

`basic` centralizes startup and interpreter errors through `Ctx` and `SystemErrorBroadcast`.

## Boot Handlers

`Boot::__construct()` registers:

- an error handler that wraps PHP errors in `pms\exception\ErrorException`
- an exception handler that stores thrown exceptions

Both call:

```php
pms_error_set($exception);
```

## Error Context

`pms_error_set()` stores the exception in `Ctx` under `error` and triggers `pms\broadcast\SystemErrorBroadcast`.

```php
function pms_error_set(Throwable $exception): void
{
    \pms\facade\Ctx::set('error', $exception);
    \pms\broadcast\SystemErrorBroadcast::trigger();
}
```

`Ctx` upper-cases keys internally, so `error` is stored as `ERROR`.

## Broadcast Handling

`SystemErrorBroadcast` extends `BroadcastApp`. Other packages can listen:

```php
SystemErrorBroadcast::listener(function () {
    $error = pms_error();
    // handle or clear the error
});
```

If a listener fully handles the exception, it should call `pms_error_clear()`.

## Final Rethrow

`Boot::handleError()` checks `pms_error()` after interpreter dispatch and during destruction.

If an error remains:

1. It prints `无任何后置方法处理此异常，最终由Boot::__destruct抛出`.
2. It clears the stored error.
3. It rethrows the exception.

## Early Exit Cases

Some startup failures exit directly instead of throwing:

- Missing `boot.json`
- Invalid `boot.json`
- Missing interpreter name in `InterpreterHook::run()`

Container, service, and PHP validation failures throw exceptions and therefore enter the error context path.
