# Installation and Package Shape

`superpms/basic` is a composer package named `superpms/basic`. Its current package metadata is defined in `composer.json`.

## Requirements

- PHP `>=8.2`
- Composer autoloading

The package declares only PHP as a runtime dependency. Development dependencies include Swoole IDE helper, selected PHP extensions for development metadata, and Symfony VarDumper.

## Autoload

Composer registers two entry surfaces:

```json
{
  "autoload": {
    "files": [
      "bin/autoload.php"
    ],
    "psr-4": {
      "pms\\": "src/pms/"
    }
  }
}
```

`autoload.files` loads global constants and helpers before project code depends on them. PSR-4 resolves framework classes under the `pms\` namespace.

## Runtime Entry

A project using this package normally requires the Composer autoloader and creates `pms\Boot` from the project root:

```php
<?php

namespace pms;

require __DIR__ . '/../vendor/autoload.php';

(new Boot(__DIR__ . '/../'))->http;
```

The property access after `Boot` construction is interpreter dispatch. `basic` does not provide the `http` or `terminal` interpreter implementations; other interpreter packages mount them with `pms\hook\InterpreterHook`.

## Local Package Structure

- `bin/autoload.php`: package file-autoload entry.
- `bin/const.php`: global content-type and lifecycle constants.
- `bin/helper.php`: global helper functions.
- `bin/swoole_define.php`: null Swoole constants for non-Swoole environments.
- `src/pms/Boot.php`: project boot and interpreter dispatch.
- `src/pms/facade/`: facade classes for core drivers.
- `src/pms/hook/`: hook containers.
- `src/pms/app/`: base app classes for extension points.
- `src/pms/program/`: core drivers used by facades.
