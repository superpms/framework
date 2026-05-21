# Hooks

Hooks are static containers used by packages to mount behavior into the runtime.

## LifecycleHook

`pms\hook\LifecycleHook` extends `LifecycleHookApp`.

Its initial container contains the boot setup handler:

```php
LIFECYCLE_BOOT => [
    [\pms\program\boot\Setup::class, 'entry'],
]
```

`LifecycleHookApp::mount($lifecycles, $callable)` mounts a callable to existing lifecycle keys. It returns `false` if the lifecycle key does not already exist or the callable cannot be resolved.

`LifecycleHookApp::run($lifecycle, ...$args)` runs every callable mounted for that lifecycle.

## InterpreterHook

`InterpreterHook::mount($name, $class)` registers one interpreter class under a name. Duplicate names are ignored and return `false`.

`InterpreterHook::run($name)` calls `ClassName::entry()`. Missing names exit with `解释器 [name] 未安装`.

Interpreter classes should extend `pms\app\InterpreterApp` or at least satisfy the same static `entry(): mixed` contract.

## AutoloadHook

`AutoloadHook::mount($filePaths)` accepts:

- string path
- array of paths/closures
- closure

String paths are first resolved with `Path::getRoot($filePath)`. If that does not resolve but the string is already a file path, it is used directly. Otherwise an exception is thrown.

`AutoloadHook::run()` requires string files once and executes closures.

## Annotation Hooks

`AnnotationClassHook::mount($attributeClass, $closure)` registers a class-attribute handler.

`AnnotationPropertyHook::mount($attributeClass, $closure)` registers a property-attribute handler.

Both hooks expose `audit()` to inspect the mounted handlers.

## Audit

Every hook implements or inherits `audit()`. Use it when debugging mounted runtime behavior:

```php
LifecycleHook::audit();
InterpreterHook::audit();
AutoloadHook::audit();
AnnotationPropertyHook::audit();
```
