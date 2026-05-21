# Composer Autoload

`superpms/basic` relies on Composer for both class loading and early global symbol loading.

## File Autoload

`composer.json` declares:

```json
"files": [
  "bin/autoload.php"
]
```

`bin/autoload.php` requires:

```php
require_once __DIR__ . '/const.php';
require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/swoole_define.php';
```

This means constants and helper functions are available once the project requires Composer's `vendor/autoload.php`.

## PSR-4 Autoload

The `pms\` namespace maps to `src/pms/`:

```json
"psr-4": {
  "pms\\": "src/pms/"
}
```

Examples:

- `pms\Boot` -> `src/pms/Boot.php`
- `pms\facade\Config` -> `src/pms/facade/Config.php`
- `pms\hook\LifecycleHook` -> `src/pms/hook/LifecycleHook.php`

## Project Autoload

Project-level autoloading is separate from Composer autoloading.

During setup, `Setup::initAutoload()` mounts `BootOptions::get_autoload()` into `AutoloadHook` and runs it. String paths are resolved against `Path::getRoot()`. Closures can also be mounted.

Use `boot.json` `autoload` for project bootstrap files that must run after `Path` exists but before config/service activation completes.

## Adjacent Package Autorun

`basic` does not include `bin/autorun.php`. Other packages can use their own autoload/autorun files to mount interpreters, services, adapters, or hooks into the containers provided here.
