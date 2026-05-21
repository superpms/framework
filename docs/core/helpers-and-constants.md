# Helpers and Constants

Global constants and helper functions are loaded by `bin/autoload.php` through Composer `autoload.files`.

## Constants

`bin/const.php` defines content-type constants:

- `JSON_CONTENT_TYPE`
- `JSONP_CONTENT_TYPE`
- `ZIP_CONTENT_TYPE`
- `PDF_CONTENT_TYPE`
- `PLAIN_CONTENT_TYPE`
- `HTML_CONTENT_TYPE`
- `CSS_CONTENT_TYPE`
- `JAVASCRIPT_CONTENT_TYPE`
- `XML_CONTENT_TYPE`
- `PNG_CONTENT_TYPE`
- `JPEG_CONTENT_TYPE`
- `MPEG_CONTENT_TYPE`

It also defines:

- `ARG_VALUE_SYMBOL`
- `LIFECYCLE_BOOT`
- `LIFECYCLE_BOOTED`
- `LIFECYCLE_SERVER_BOOTED`
- `LIFECYCLE_SANDBOX_CREATED`
- `LIFECYCLE_SANDBOX_BOOT`
- `LIFECYCLE_SANDBOX_BOOTED`
- `LIFECYCLE_SANDBOX_RAN`
- `LIFECYCLE_SANDBOX_DESTRUCT`

## Environment Helpers

- `in_console()`
- `in_swoole()`
- `is_dev()`
- `is_fullpath()`

`is_dev()` checks for `dev.lock` under `Path::getRoot()`.

## Config and Path Helpers

- `config($name = null, $default = null)`
- `path_join(...$segments)`
- `path_class($path)`

`path_join()` normalizes separators and resolves `.` and `..` segments.

## Config File Helpers

- `load_json_config($file, $associative = true)`
- `save_json_config($file, $value, $flags = 448)`
- `load_php_config($file)`
- `save_php_config($file, array $data)`
- `load_ini_config($file)`
- `load_file_config($filePaths)`

`load_file_config()` supports PHP, JSON, and INI files. INI files with the same filename are deep-merged; PHP and JSON files replace the previous value for that key.

## Data Helpers

- `array_chain()`
- `array_chain_set()`
- `object_chain()`
- `object_chain_set()`
- `array_to_xml()`
- `array_merge_deep()`
- `str_to_fn()`
- `bit_or()`
- `valid_datatype_or()`
- `value_compare()`

## File and Process Helpers

- `dir_create()`
- `file_create()`
- `has_process()`
- `call_php_script()`

`call_php_script()` uses `popen()` to start a command in the background. Projects that rely on it should include `popen` in boot validation.

## CallablePlus Helpers

- `transform_callable_plus()`
- `is_callable_plus()`
- `callable_plus()`

A callable-plus value stores a callable as the first array item and arguments after it. `callable_plus()` validates and then calls it.

## Debug and Error Helpers

- `dd(...$vars)`
- `pms_error_set(Throwable $exception)`
- `pms_error()`
- `pms_error_clear()`

`dd()` dumps with Symfony VarDumper. In CLI it throws `CliModeForcedInterruptException`; outside CLI it sends a 500 response and terminates.

## Swoole Constants

`bin/swoole_define.php` defines common Swoole constants as `null` when `SWOOLE_VERSION` is not defined. This allows code to reference those constants in non-Swoole environments without fatal constant errors.
