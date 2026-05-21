# ServiceApp

`pms\app\ServiceApp` is the declarative way to activate package behavior from configuration.

During boot, `Setup::initService()` reads `config('service', [])` and passes it to `Service::init()`. `pms\program\service\Driver` registers each service class and activates it.

## Service Class Shape

A service must extend `ServiceApp` and provide a static `entry()` method:

```php
use pms\app\ServiceApp;
use pms\hook\LifecycleHook;

class ExampleService extends ServiceApp
{
    public static string $hookClass = LifecycleHook::class;
    public static string $lifecycle = LIFECYCLE_BOOTED;
    public static bool $ignore = false;
    public static false|string|array $adapter = false;

    public static function entry(): void
    {
        // package-level setup
    }
}
```

## Lifecycle Service

When `$adapter === false`, the service is mounted into a lifecycle hook:

```php
$hook::mount($lifecycle, [$serviceClass, 'entry']);
```

The hook class must be a subclass of `pms\app\LifecycleHookApp`.

## Adapter Service

When `$adapter` is a string or array, the service is mounted into an adapter hook:

```php
$hook::mount($lifecycle, $adapter, [$serviceClass, 'entry']);
```

The hook class must be a subclass of `pms\app\AdapterApp`.

## Failure Rules

Activation returns `false` when the service class is empty, missing, or not a `ServiceApp`.

Activation throws when:

- `$hookClass` does not exist and `$ignore` is false.
- `entry()` is not callable.
- A lifecycle service targets a hook that is not a `LifecycleHookApp`.
- An adapter service targets a hook that is not an `AdapterApp`.

If `$hookClass` is missing and `$ignore` is true, activation quietly returns `false`.

## Config Form

The service config may be a single class name or an array. Named array keys are preserved during registration and can be used with `Service::activeName($name)`.
