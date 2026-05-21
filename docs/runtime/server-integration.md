# Server Integration

This page records how the current server project reaches `superpms/basic`. It is intentionally limited to entrypoint facts and package boundaries.

## HTTP Entry

The server HTTP public entry uses the project Composer autoloader and dispatches the `http` interpreter:

```php
<?php

namespace pms;

require __DIR__ . '/../vendor/autoload.php';

(new Boot(__DIR__ . "/../"))->http;
```

## Terminal Entry

The server terminal entry uses the same pattern and dispatches `terminal`:

```php
#!/usr/bin/env php
<?php

namespace pms;

require './vendor/autoload.php';

(new Boot(__DIR__))->terminal;
```

## Project boot.json

The server project's `boot.json` provides the paths, autoload files, debug flags, PHP ini values, validation list, and `extend` settings that `basic` reads during boot.

The current server uses `extend` keys for adjacent extension packages, and uses `autoload` to load project-level constants and common functions.

## Boundary

`basic` only provides the boot, hook, facade, config, context, path, service, container, and helper foundation. HTTP routing, terminal command parsing, package-specific services, and business modules are mounted by the server project or adjacent packages.
