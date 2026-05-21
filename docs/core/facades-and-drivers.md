# Facades and Drivers

`pms\Facade` provides static access to driver instances. The facade class chooses its driver by implementing `getFacadeClass()`.

## Facade Cache

`Facade::createFacade()` creates and caches one driver instance per driver class unless a new instance is explicitly requested. Static calls are forwarded to the cached driver:

```php
Config::get('service', []);
Path::getRuntime('/base/error.log');
Ctx::set('error', $exception);
```

## Core Facades

| Facade | Driver | Role |
| --- | --- | --- |
| `pms\facade\BootOptions` | `pms\program\boot\Driver` | Parsed `boot.json` options. |
| `pms\facade\Config` | `pms\program\config\Driver` | Project configuration store. |
| `pms\facade\Ctx` | `pms\program\ctx\Driver` | Runtime context store. |
| `pms\facade\Path` | `pms\program\path\Driver` | Named project paths. |
| `pms\facade\Service` | `pms\program\service\Driver` | Service registration and activation. |

## BootOptions Driver

`pms\program\boot\Driver` extends `OptionsAccessCfg`. Keys are stored upper-cased, but this driver overrides the base required-missing behavior with `missing = false`; absent dynamic getters return the supplied default or `null`. It exposes dynamic getters such as:

```php
BootOptions::get_timezone();
BootOptions::get_dir_app();
BootOptions::get_php_ini();
BootOptions::get_extend('plugins');
```

`Driver::init($boot)` normalizes all fields used during setup and stores `extend` for other packages.

## OptionsAccess

`OptionsAccess` implements:

- `JsonSerializable`
- `Iterator`
- `ArrayAccess`
- `Countable`

It also provides collection-style helpers: `column()`, `map()`, `filter()`, `reduce()`, `each()`, `keys()`, and `has()`.

Dynamic calls beginning with `get_` and `set_` are translated to internal key access.
