# superpms/basic

`superpms/basic` is the runtime foundation package for SuperPMS composer modules. It provides the boot sequence, global helpers and constants, facades, hook containers, configuration/path/context/service drivers, and the base contracts used by adjacent interpreter and program packages.

This package targets PHP `>=8.2`, registers `pms\` through PSR-4 at `src/pms/`, and loads `bin/autoload.php` through Composer `autoload.files`.

## What It Provides

- `pms\Boot` for reading project `boot.json`, initializing runtime options, running boot lifecycle hooks, and dispatching named interpreters.
- Core facades: `BootOptions`, `Config`, `Ctx`, `Path`, and `Service`.
- Hook families for lifecycle, interpreter, autoload, class attributes, and property attributes.
- Base app classes for interpreters, lifecycle hooks, services, adapters, and broadcasts.
- Container construction, constructor injection, and `#[pms\annotate\Inject]` property injection.
- Global constants and helpers loaded by Composer.

`basic` does not implement HTTP, terminal, Swoole, database, cache, workflow, shop, user, auth, filesystem, or other business features. Those are provided by the project or adjacent packages that mount into this foundation.

## Documentation

Start with [docs/README.md](docs/README.md).

The detailed docs are organized by module:

- [Getting started](docs/getting-started/installation.md)
- [Architecture](docs/architecture/boot-lifecycle.md)
- [Core APIs](docs/core/facades-and-drivers.md)
- [Extension points](docs/extension-points/hooks.md)
- [Runtime behavior](docs/runtime/error-handling.md)
- [Reference](docs/reference/api-map.md)

## Installation

```bash
composer require superpms/basic
```

In a SuperPMS project, this package is usually reached through the project Composer autoloader:

```php
<?php

namespace pms;

require __DIR__ . '/../vendor/autoload.php';

(new Boot(__DIR__ . '/../'))->http;
```

The `http` or `terminal` interpreter names are not built into this package. They are mounted by interpreter packages with `InterpreterHook`.

## License

Apache-2.0.
