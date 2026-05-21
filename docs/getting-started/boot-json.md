# boot.json Contract

`pms\Boot` reads `boot.json` from the project root passed to its constructor. If no root is passed, it infers a root by walking up from the installed vendor package path.

## Required File

During `Boot::__construct()`:

1. The root path is normalized with `path_join()`.
2. `boot.json` is loaded from that root.
3. Missing file exits with `系统引导文件不存在`.
4. Invalid JSON exits with `系统引导文件读取错误`.
5. Parsed JSON is passed to `BootOptions::init($bootConfig)`.
6. `LifecycleHook::run(LIFECYCLE_BOOT, $rootPath)` starts setup.

## Fields Used By basic

`pms\program\boot\Driver` maps the JSON object into `BootOptions`.

| Field | Default | Used for |
| --- | --- | --- |
| `timezone` | `Asia/Shanghai` | `date_default_timezone_set()` during setup. |
| `dir.app` | `app` | Project application path. |
| `dir.config` | `config` | Project config path scanned by `Config::fetchConfig()`. |
| `dir.runtime` | `runtime` | Runtime path and log path root. |
| `autoload` | `[]` | Project files or closures loaded by `AutoloadHook`. |
| `error_debug` | `true` | When false, setup disables `display_errors`. |
| `log_debug` | `false` | When true, setup enables PHP error logging to runtime. |
| `php.ini` | `{}` | Key-value list applied with `ini_set()`. |
| `php.validate.extension` | `[]` | Extension names checked by `extension_loaded()`. |
| `php.validate.function` | `[]` | Function names checked by `function_exists()`. |
| `extend` | `{}` | Extra package/project settings available through `BootOptions::get_extend()`. |

Validation entries starting with `#` are skipped.

## Minimal Example

```json
{
  "timezone": "Asia/Shanghai",
  "dir": {
    "app": "/app",
    "config": "/core/config",
    "runtime": "/runtime"
  },
  "autoload": [
    "/core/common.php"
  ],
  "error_debug": true,
  "log_debug": false,
  "php": {
    "ini": {
      "memory_limit": "1024M"
    },
    "validate": {
      "function": [
        "popen"
      ]
    }
  }
}
```

Paths in `dir` and `autoload` are resolved against the project root through `Path` and `AutoloadHook`.
