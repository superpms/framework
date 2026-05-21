# Interpreters and Adapters

`basic` provides the base classes and hook containers for interpreters and adapters. It does not provide concrete HTTP or terminal interpreters.

## Interpreters

Interpreters are named runtime entrypoints. `Boot::__get($name)` dispatches through `InterpreterHook::run($name)`.

An interpreter class should expose:

```php
public static function entry(): mixed;
```

Typical mounting from an adjacent package:

```php
use pms\hook\InterpreterHook;

InterpreterHook::mount('http', HttpInterpreter::class);
InterpreterHook::mount('terminal', TerminalInterpreter::class);
```

Then a project can call:

```php
(new Boot($root))->http;
(new Boot($root))->terminal;
```

Because `InterpreterHook::mount()` refuses duplicate names, package load order matters when multiple packages try to claim the same interpreter name.

## AdapterApp

`pms\app\AdapterApp` is a keyed hook container. It is more specific than a lifecycle hook because each lifecycle can contain named adapters and each adapter key maps to one callable.

```php
AdapterHook::mount($lifecycle, 'adapter-name', CallableClass::class);
AdapterHook::run($lifecycle, 'adapter-name', ...$args);
```

If a non-callable string class is passed, `AdapterApp` resolves it to `[ClassName::class, 'entry']`.

Mounting returns `false` when:

- The lifecycle key does not exist in the adapter hook container.
- The callable cannot be resolved.

Running returns `false` when the lifecycle or adapter key is missing.

## When To Use Which

- Use an interpreter when a project entrypoint should hand control to a runtime mode, such as HTTP or terminal.
- Use a lifecycle hook when every mounted handler for a lifecycle should run.
- Use an adapter when a lifecycle has named replaceable connection points and only one callable should handle a given adapter key.
