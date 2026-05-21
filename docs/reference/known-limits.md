# Known Limits

These limits are intentional package boundaries or current implementation facts.

## No Concrete Business Features

`basic` does not implement business domains. Do not document cashier, shop, workflow, filesystem, user, auth, or similar server business behavior as features of this package.

Those systems may use `basic`, but their rules belong in their own modules.

## No Concrete Interpreters

`basic` provides `InterpreterHook`, `InterpreterApp`, and `Boot` dispatch. It does not implement `http`, `terminal`, or Swoole interpreters. Missing interpreter names exit directly.

## No Built-In Tests Or Examples

This package currently has no local `tests/` or `examples/` directory and no package-specific test suite was found.

## Lifecycle Keys Must Exist

`LifecycleHookApp::mount()` and `AdapterApp::mount()` refuse lifecycle keys that are not already present in the hook container. A dynamic lifecycle requires a hook subclass that predefines the key or custom mount behavior.

## Duplicate Mounting Is Limited

- `InterpreterHook::mount()` ignores duplicate interpreter names.
- Annotation hooks ignore duplicate attribute classes.
- `AdapterApp::mount()` overwrites the same adapter key under the same lifecycle.
- `LifecycleHookApp::mount()` appends callables for an existing lifecycle.

## Early Exit Behavior

Missing `boot.json`, invalid `boot.json`, and missing interpreters use `exit(...)` rather than exceptions. Code that needs recoverable behavior must handle this at a higher process boundary.
