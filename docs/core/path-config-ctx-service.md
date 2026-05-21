# Path, Config, Ctx, and Service

These four drivers are the core runtime state surfaces created during boot.

## Path

`pms\program\path\Driver` stores named paths in upper-case keys.

Setup initially mounts:

```php
Path::init([
    'root' => $rootPath,
    'app' => path_join($rootPath, BootOptions::get_dir_app()),
    'config' => path_join($rootPath, BootOptions::get_dir_config()),
    'runtime' => path_join($rootPath, BootOptions::get_dir_runtime()),
]);
```

Access is dynamic:

```php
Path::getRoot('/boot.json');
Path::getConfig();
Path::getRuntime('/base/error.log');
```

`Path::mount($name, $path)` can add package or project paths. Mounting creates the directory if it does not exist.

## Config

`pms\program\config\Driver` loads project config files from one or more directories.

`Config::fetchConfig($paths)` scans:

- `*.php`
- `*.ini`
- `dev/*.php` and `dev/*.ini` when `is_dev()` is true

The loaded files are keyed by lowercase filename without extension. INI files are parsed with typed values and dot-chain expansion.

Access examples:

```php
config();
config('service', []);
Config::get('database.default');
Config::merge(['feature' => ['enabled' => true]]);
```

When running in Swoole and `fetchConfig(..., true)` or `merge(..., true)` is used, coroutine-local config is stored through `Ctx`.

## Ctx

`pms\program\ctx\Driver` is a runtime context store.

In non-Swoole mode it uses an in-memory array. In Swoole mode it stores values in coroutine context when a coroutine id is active, and falls back to the global container outside a coroutine.

Keys are upper-cased:

```php
Ctx::set('error', $exception);
Ctx::get('ERROR');
Ctx::has('config');
```

## Service

`pms\program\service\Driver` reads service class names, registers them, and activates them.

Setup calls:

```php
$cfg = config('service', []);
Service::init(is_array($cfg) ? $cfg : [$cfg]);
```

Activation requires the class to extend `pms\app\ServiceApp`. A service declares:

- `public static string $hookClass`
- `public static string $lifecycle`
- `public static bool $ignore = false`
- `public static false|string|array $adapter = false`

If `$adapter` is `false`, the service is mounted as a lifecycle hook. If `$adapter` is set, the service is mounted into an adapter hook.
