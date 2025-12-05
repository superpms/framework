<?php

use pms\facade\Path;

if (!function_exists('custom_error_handler')) {
    function custom_error_handler($errno, $errstr, $errfile, int $errline) {
        throw new \pms\exception\ErrorException($errno, $errstr, $errfile, $errline);
    }
}

function pms_error_set(Throwable $exception): void
{
    \pms\facade\Ctx::set('error', $exception);
    \pms\broadcast\SystemErrorBroadcast::trigger();
}

function pms_error(): null|Throwable
{
    return \pms\facade\Ctx::get('error');
}

function pms_error_clear(): void
{
    \pms\facade\Ctx::set('error', null);
}


if (!function_exists('dd')) {
    function dd(mixed ...$vars): void {
        if (!in_console() && !headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/html');
        }
        if (array_key_exists(0, $vars) && 1 === count($vars)) {
            \Symfony\Component\VarDumper\VarDumper::dump($vars[0]);
        } else {
            foreach ($vars as $k => $v) {
                \Symfony\Component\VarDumper\VarDumper::dump($v, is_int($k) ? 1 + $k : $k);
            }
        }
        if (!in_console()) {
            die;
        } else {
            throw new \pms\exception\CliModeForcedInterruptException("die");
        }
    }
}

if (!function_exists('json_validate')) {
    /**
     * 判断是否为有效json数据
     *
     * @param string $string 数据
     * @return bool
     */
    function json_validate(string $string): bool {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('in_console')) {
    function in_console(): bool {
        return in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true);
    }
}
if (!function_exists('in_swoole')) {
    function in_swoole(): bool {
        return in_console() && defined('SWOOLE_VERSION') && SWOOLE_VERSION !== null;
    }
}

if (!function_exists('config')) {
    function config(?string $name = null, $default = null) {
        return \pms\facade\Config::get($name, $default);
    }
}
if (!function_exists('is_dev')) {
    function is_dev(): bool {
        return file_exists(Path::getRoot("/dev.lock"));
    }
}

if (!defined('DIRECTORY_SEPARATOR_REVERSE')) {
    define('DIRECTORY_SEPARATOR_REVERSE', DIRECTORY_SEPARATOR === '/' ? '\\' : '/');
}

if (!function_exists('path_join')) {
    function path_join(...$segments): string {
        $segments = array_filter($segments);
        $path     = array_map(
            function ($segment) {
                if (is_array($segment)) {
                    $segment = path_join(...$segment);
                }
                return str_replace(DIRECTORY_SEPARATOR_REVERSE, DIRECTORY_SEPARATOR, $segment);
            }, $segments
        );

        $parts = explode(DIRECTORY_SEPARATOR, join(DIRECTORY_SEPARATOR, $path));
        $stack = [];

        foreach ($parts as $key => $part) {
            // 忽略空段和当前目录
            if (($key !== 0 && $part === '') || $part === '.') continue;

            // 处理上级目录
            if ($part === '..') {
                if (!empty($stack)) array_pop($stack);
                continue;
            }

            $stack[] = $part;
        }
        // 重新组合路径
        return implode(DIRECTORY_SEPARATOR, $stack);
    }
}
if (!function_exists('path_class')) {
    function path_class(string $path): string {
        $path = str_replace(DIRECTORY_SEPARATOR, '\\', $path);
        return str_replace('.php', '', $path);
    }
}

if (!function_exists('array_chain')) {
    function array_chain(array $data, string $chain, string $chainLevelStr = '.') {
        $tmp = $data;
        $fA  = explode($chainLevelStr, $chain);
        for ($i = 0; $i < count($fA); $i++) {
            $key = $fA[$i];
            if (isset($tmp[$key])) {
                $tmp = $tmp[$key];
            } else {
                return null;
            }
        }
        return $tmp;
    }
}
if (!function_exists('array_chain_set')) {
    function array_chain_set(array &$data, string $chain, mixed $value, string $chainLevelStr = '.'): void {
        $result  = &$data;
        $current = &$result;

        $fA = explode($chainLevelStr, $chain);
        if (count($fA) === 1) {
            $result[$chain] = $value;
            return;
        }
        for ($i = 0; $i < count($fA); $i++) {
            $item = $fA[$i];
            if (!isset($current[$item])) {
                $current[$item] = [];
            }
            if ($i === count($fA) - 1) {
                $current[$item] = $value;
            } else {
                if (!is_array($current[$item])) {
                    throw new \Exception('chain_set: ' . $item . ' is not a ordinary object in chain ' . $chain);
                }
                $current = &$current[$item];
            }
        }
        return;
    }
}
if (!function_exists('array_to_xml')) {
    function array_to_xml(array|object $array, string $root = 'root'): bool|string {
        function arrayToXml($array, &$xml): void {
            foreach ($array as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    if (!is_numeric($key)) {
                        $subnode = $xml->addChild($key);
                        arrayToXml($value, $subnode);
                    } else {
                        arrayToXml($value, $xml);
                    }
                } else {
                    $xml->addChild($key, $value);
                }
            }
        }

        $xml = new \SimpleXMLElement('<' . $root . '/>');
        arrayToXml($array, $xml);
        return $xml->saveXML();
    }
}
if (!function_exists('array_merge_deep')) {
    function array_merge_deep(...$args): array {
        $result = [];
        foreach ($args as $array) {
            if (is_array($array)) {
                foreach ($array as $key => $value) {
                    if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                        $result[$key] = array_merge_deep($result[$key], $value);
                    } else {
                        $result[$key] = $value;
                    }
                }
            } else {
                $result[] = $array;
            }
        }
        return $result;
    }
}


if (!function_exists('object_chain')) {
    function object_chain(array $data, string $chain, string $chainLevelStr = '.') {
        return array_chain($data, $chain, $chainLevelStr);
    }
}
if (!function_exists('object_chain_set')) {
    function object_chain_set(array &$data, string $chain, mixed $value, string $chainLevelStr = '.'): void {
        array_chain_set($data, $chain, $chainLevelStr);
    }
}

if (!function_exists('config_load_php')) {
    function config_load_php(string $file) {
        if (is_file($file)) {
            return include $file;
        }
        return [];
    }
}
if (!function_exists('config_load_ini')) {
    function config_load_ini(string $file): array {
        if (!is_file($file)) {
            return [];
        }
        $file    = file_get_contents($file);
        $fileArr = explode("\r\n", $file);

        $fileStr = "";
        foreach ($fileArr as $value) {
            if (str_starts_with($value, '#')) {
                continue;
            }
            $fileStr .= $value . "\r\n";
        }

        $info = parse_ini_string($fileStr, true, INI_SCANNER_TYPED);

        if ($info === false) {
            return [];
        }
        $data = [];
        foreach ($info as $key => $value) {
            if ($key === '/') {
                $data = [
                    ...$data,
                    ...$value,
                ];
                continue;
            }
            $data[$key] = $value;
        }
        $tmp = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    array_chain_set($tmp, $key . '.' . $key2, $value2);
                }
            } else {
                array_chain_set($tmp, $key, $value);
            }
        }
        return $tmp;
    }
}
if (!function_exists('load_file_config')) {
    function load_file_config(string|array $filePath): array {
        if (is_string($filePath)) {
            $filePath = [$filePath];
        }
        $config = [];
        foreach ($filePath as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (is_file($file)) {
                $name      = strtolower($name);
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $tmp       = [];
                switch ($extension) {
                    case 'php':
                        $tmp = config_load_php($file);
                        break;
                    case 'json':
                        $tmp = json_decode(file_get_contents($file), true);
                        break;
                    case 'ini':
                        $tmp = config_load_ini($file);
                        break;
                }
                if (!isset($config[$name])) {
                    $config[$name] = $tmp;
                } else {
                    if ($extension === 'ini') {
                        if (!empty($tmp)) {
                            $config[$name] = array_merge_deep($config[$name], $tmp);
                        }
                    } else {
                        $config[$name] = $tmp;
                    }
                }
            }
        }
        return $config;
    }
} else {
    throw new \Exception("PMS 核心函数 load_file_config 被重载");
}

if (!function_exists('str_to_fn')) {
    function str_to_fn(string|array $fnName, $value): mixed {
        if (is_array($fnName)) {
            $fnName = join('|', $fnName);
        }
        $fnName = strtoupper($fnName);
        $fnName = str_replace('|', ',', $fnName);
        $fnName = explode(',', $fnName);
        foreach ($fnName as $item) {
            $item = trim($item);
            switch ($item) {
                case 'MD5':
                    $value = md5($value);
                    break;
                case 'STRTOTIME':
                    $value = strtotime($value);
                    break;
                case "INT":
                case "STRTOINT":
                    $value = intval($value);
                    break;
                case "NUMBER":
                case "STRTONUMBER":
                    $value = (float)$value;
                    break;
                case "DOUBLE":
                case "STRTODOUBLE":
                    $value = floatval($value);
                    break;
                case "STR":
                case "STRING":
                    $value = (string)$value;
                    break;
                case "TOJSONSTR":
                case "TOJSONSTRING":
                    $value = json_encode($value);
                    break;
                case "JSONSTRTOARRAY":
                case "JSONSTRTOARR":
                case "JSONSTRINGTOARRAY":
                case "JSONSTRINGTOARR":
                    if (is_string($value) && json_validate($value)) {
                        $value = json_decode($value, true);
                    }
                    break;
                case "JSONSTRTOOBJECT":
                case "JSONSTRTOOBJ":
                case "JSONSTRINGTOOBJECT":
                case "JSONSTRINGTOOBJ":
                    if (is_string($value) && json_validate($value)) {
                        $value = json_decode($value);
                    }
                    break;
                case 'PARSESTR':
                    if (is_string($value)) {
                        $newValue = [];
                        parse_str($value, $newValue);
                        $value = $newValue;
                    }
                    break;
                case 'STRTOUPPER':
                    if (is_string($value)) {
                        $value = strtoupper($value);
                    }
                    break;
                case 'STRTOLOWER':
                    if (is_string($value)) {
                        $value = strtolower($value);
                    }
                    break;
            }
        }
        return $value;
    }
}

if (!function_exists('bit_or')) {
    /**
     * 分离或运算和值
     * @param array $keyMap 或运算索引表(所有能进行或运算的原子值集合)[1,2,4,...]
     * @param int   $value  和值(通过索引表中包含的数进行的任意或运算)
     * @return array
     */
    function bit_or(array $keyMap, int $value): array {
        $result = [];
        foreach ($keyMap as $v) {
            if (($value & $v) === $v) {
                $result[] = $v;
            }
        }
        return $result;
    }
}

if (!function_exists('valid_datatype_or')) {
    /**
     * 验证数据类型(或)
     * @param string|array $type  数据类型(传入数组 或 用 | 分割多个类型)
     * @param mixed        $datum 数据
     * @return bool
     */
    function valid_datatype_or(string|array $type, mixed $datum): bool {
        try {
            if (is_array($type)) {
                $type = join('|', $type);
            }
            $type = strtoupper($type);
            $type = str_replace('||', '|', $type);
            $type = explode('|', $type);
            foreach ($type as $value) {
                $default = true;
                switch ($value) {
                    case 'INT':
                        $default = is_integer($datum);
                        break;
                    case 'DOUBLE':
                        $default = is_double($datum);
                        break;
                    case 'NUMBER':
                        $default = !is_string($datum) && is_numeric($datum);
                        break;
                    case 'STRING':
                    case 'STR':
                        $default = is_string($datum);
                        break;
                    case 'ARRAY':
                    case 'ARR':
                        $default = is_array($datum);
                        break;
                    case 'BOOL':
                    case 'BOOLEAN':
                        $default = is_bool($datum);
                        break;
                    case 'FILE':
                        $default = isset($datum['tmp_name']) && is_file($datum['tmp_name']);
                        break;
                }
                if ($default) {
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('dir_create')) {
    /**
     * 创建文件夹
     * @param string $path        文件夹地址
     * @param int    $permissions 权限, 默认0777
     * @return bool
     */
    function dir_create(string $path, int $permissions = 0777): bool {
        if (file_exists($path)) {
            return false;
        }
        @mkdir($path, $permissions, true);
        return true;
    }
}
if (!function_exists('file_create')) {
    /**
     * 创建文件
     * @param string $path    文件地址
     * @param string $data    文件数据
     * @param string $mode    文件模式, 默认w，可选w,a,,x,
     *                        ‘w'：创建文件。如果文件存在则覆盖内容。
     *                        ‘a'：创建文件。如果文件存在则追加内容。
     *                        ‘x'：创建文件，在文件不存在时才创建。
     * @return bool
     */
    function file_create(string $path, string $data, string $mode = "w"): bool {
        dir_create(pathinfo($path)['dirname']);
        $file = fopen($path, $mode);
        if (!$file) {
            return false;
        }
        fwrite($file, $data);
        fclose($file);
        return true;
    }
}

if (!function_exists('class_annotate_attrs')) {
    /**
     * @param ReflectionClass $class
     * @param string          $name
     * @param bool            $final 是否只返回最后一项
     * @return ReflectionAttribute|ReflectionAttribute[]
     */
    function class_annotate_attrs(ReflectionClass $class, string $name, bool $final = false): array|ReflectionAttribute {
        // 获取当前类的属性
        $attrs = $class->getAttributes($name);
        if ($final && !empty($attrs)) {
            return $attrs[count($attrs) - 1];
        }
        // 获取当前类 所有 trait 中的注解属性
        foreach (array_reverse($class->getTraits()) as $trait) {
            $child = class_annotate_attrs($trait, $name, $final);
            $child = $final ? [$child] : $child;
            $attrs = [
                ...$child,
                ...$attrs,
            ];
            if ($final && !empty($attrs)) {
                return $attrs[count($attrs) - 1];
            }
        }
        
        $class = $class->getParentClass();
        if ($class) {
            $child = class_annotate_attrs($class, $name, $final);
            $child = $final ? [$child] : $child;
            $attrs = [
                ...$child,
                ...$attrs,
            ];
        }
        if ($final && !empty($attrs)) {
            return $attrs[count($attrs) - 1];
        }
        return $attrs;
        
    }
}


if (!function_exists('has_process')) {
    function has_process(int $pid): bool {
        if (PHP_OS === 'WINNT') {
            $output = shell_exec("tasklist /FI \"PID eq $pid\"");
            return str_contains($output, $pid);
        } else {
            if (function_exists('posix_kill')) {
                return posix_kill($pid, 0);
            }
            if (is_dir("/proc/{$pid}")) {
                return true;
            }
            return is_dir("/proc/{$pid}");
        }
    }
}

if (!function_exists('call_php_script')) {
    function call_php_script(string $path, $cmd, $logPath = null): void {
        if (PHP_OS === 'WINNT') {
            $handle = popen("cd {$path} &&start /B $cmd > $logPath", 'r');
        } else {
            $handle = popen("cd {$path} && nohup $cmd > $logPath 2>&1 &", 'r');
        }
        pclose($handle);
    }
}


if (!function_exists('transform_callable_plus')) {
    /**
     * 将 callable/callablePlus 统一转换为 callablePlus 可执行对象
     * @param mixed $value
     * @return false|array
     */
    function transform_callable_plus(mixed $value): false|array {
        if (is_callable($value)) {
            return [$value];
        }
        if (is_string($value)) {
            if (class_exists($value)) {
                return [new $value()];
            }
        }
        if (is_array($value)) {
            $fn = array_shift($value);
            if (is_callable($fn)) {
                return [$fn, ...$value];
            }
            if (class_exists($fn)) {
                return [new $fn(), ...$value];
            }
            if (count($value) > 0) {
                $fnName = array_shift($value);
                if (is_callable([$fn, $fnName])) {
                    return [[$fn, $fnName], ...$value];
                }
            }
        }
        return false;
    }
}


if (!function_exists('is_callable_plus')) {
    /**
     * 判断是否为CallablePlus可执行对象
     * @param mixed $value
     * @return bool
     */
    function is_callable_plus(mixed $value): bool {
        if (empty($value)) {
            return false;
        }
        if (!is_array($value)) {
            return false;
        }
        return is_callable($value[0]);
    }
}


if (!function_exists('callable_plus')) {
    /**
     * 执行CallablePlus可执行对象
     * @param mixed $value
     * @param bool  $validate 是否进行验证(若提前已经验证,可传入false,减少性能开销)
     * @return mixed
     * @throws Exception
     */
    function callable_plus(mixed $value, bool $validate = true): mixed {
        if ($validate && !is_callable_plus($value)) {
            throw new \Exception("Not a callable plus");
        }
        $call = function (callable $fn, ...$args) {
            return call_user_func_array($fn, $args);
        };
        return $call(...$value);
    }

}

if (!function_exists('value_compare')) {
    /**
     * 比较两个值
     * @param mixed  $value1
     * @param string $symbol
     * @param mixed  $value2
     * @return bool
     */
    function value_compare(mixed $value1, string $symbol, mixed $value2): bool {
        return match ($symbol) {
            '=', '==', 'equal', 'eq'                => $value1 == $value2,
            '===', 'identical'                      => $value1 === $value2,
            '!=', '<>', 'not-equal', 'neq'          => $value1 != $value2,
            '!==', 'not_identical', 'not identical' => $value1 !== $value2,
            '>', 'greater'                          => $value1 > $value2,
            '<', 'less'                             => $value1 < $value2,
            '>=', 'greater_equal', 'greater equal'  => $value1 >= $value2,
            '<=', 'less_equal', 'less equal'        => $value1 <= $value2,
            'in'                                    => in_array($value1, $value2),
            'not_in', 'not in'                      => !in_array($value1, $value2),
            default                                 => throw new InvalidArgumentException("不支持的比较符号: $symbol"),
        };
    }
}