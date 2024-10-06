<?php

namespace pms\server\middleware;

use pms\app\inject\http\SafeParamsInject;
use pms\app\Middleware;
use pms\contract\ContainerInterface;
use pms\exception\RequestMethodException;
use pms\exception\RequestParamsException;
use pms\helper\Data;
use pms\helper\Process;
use pms\server\example\http\SafeParams;
use ReflectionMethod;

class HttpRequestMiddleware extends Middleware
{

    protected array $realParams = [];
    protected array $bitOr = [
        METHOD_GET => 'GET',
        METHOD_POST => "POST",
        METHOD_PUT => "PUT",
        METHOD_DELETE => "DELETE",
        METHOD_PATCH => "PATCH",
        METHOD_HEAD => "HEAD",
    ];

    /**
     * @throws \ReflectionException
     */
    public function handle(): void
    {
        $methodPy = $this->class->getProperty('method');
        $method = 'GET';
        if ($methodPy->hasDefaultValue()) {
            $method = $methodPy->getDefaultValue();
        }
        $this->method($method);
        $validate = [];
        $validatePy = $this->class->getProperty('validate');
        if ($validatePy->hasDefaultValue()) {
            $validate = $validatePy->getDefaultValue();
        } else {
            $methods = $this->class->getMethods(ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_STATIC);
            foreach ($methods as $fn) {
                if ($fn->getName() === 'validate') {
                    $validate = $fn->invoke(null,$this->request);
                    if (!is_array($validate)) {
                        $validate = [];
                    }
                    break;
                }
            }
        }
        $this->params($validate);
    }

    private function method($method): void
    {
        if (is_string($method)) {
            $method = strtoupper($method);
            $method = str_replace(',', '|', $method);
            $method = explode("|", $method);
            $method = [...$method];
        } else if (is_int($method)) {
            $method = Data::separateBitOr([
                METHOD_GET,
                METHOD_POST,
                METHOD_PUT,
                METHOD_DELETE,
                METHOD_PATCH,
                METHOD_HEAD,
            ], $method);
            $method = array_map(function ($v) {
                return $this->bitOr[$v];
            }, $method);
        } else if (is_array($method)) {
            $method = array_map(function ($v) {
                if (is_int($v)) {
                    return $this->bitOr[$v] ?? array_map(function ($v) {
                        return $this->bitOr[$v];
                    }, Data::separateBitOr([
                        METHOD_GET,
                        METHOD_POST,
                        METHOD_PUT,
                        METHOD_DELETE,
                        METHOD_PATCH,
                        METHOD_HEAD,
                    ], $v));
                } else {
                    return strtoupper($v);
                }
            }, $method);
        }
        $current = $this->request->method();
        $real = [];
        foreach ($method as $v) {
            if (is_array($v)) {
                $real = [...$real, ...$v];
            } else {
                $real[] = $v;
            }
        }
        if (!in_array($current, $method)) {
            throw new RequestMethodException("请求类型不正确");
        }
    }

    private function vParams(array $config, array $data): array
    {
        $realData = [];
        foreach ($config as $key => $configItem) {
            $require = $configItem['require'] ?? false;
            $datum = null;
            if (isset($data[$key]) && !Process::realEmpty($data[$key])) {
                $datum = $data[$key];
            } else if (isset($configItem['default'])) {
                if ($configItem['default'] instanceof \Closure) {
                    $datum = $configItem['default']();
                } else {
                    $datum = $configItem['default'];
                }
            }
            if (Process::realEmpty($datum)) {
                if ($require) {
                    $this->paramsException($configItem['des'] ?? ($configItem['desc'] ?? $key), $key, 1);
                }
                continue;
            }
            if (isset($configItem['transform'])) {
                if ($configItem['transform'] instanceof \Closure) {
                    $datum = $configItem['transform']($datum);
                } else {
                    $datum = Process::strToAction($configItem['transform'], $datum);
                }
            }
            if (isset($configItem['type'])) {
                $type = $configItem['type'];
                switch ($type) {
                    case 'package':
                    case 'pkg':
                        if (isset($configItem['package']) && is_array($configItem['package'])) {
                            $datum = $this->vParams($configItem['package'], $datum);
                        }
                        break;
                    case 'packages':
                    case 'pkgs':
                        if (isset($configItem['package']) && is_array($configItem['package'])) {
                            $t = [];
                            foreach ($datum as $item) {
                                $t[] = $this->vParams($configItem['package'], $item);
                            }
                            $datum = $t;
                        }
                        break;
                    case 'enum':
                        if (isset($configItem['enum']) && is_array($configItem['enum']) && !in_array($datum, $configItem['enum'])) {
                            $this->paramsException($configItem['des'] ?? $configItem['desc'] ?? $key, $key, 5, join("或", $configItem['enum']));
                        }
                        break;
                    default:
                        if (!Process::validType($type, $datum)) {
                            $this->paramsException($configItem['des'] ?? $configItem['desc'] ?? $key, $key, 2);
                        }
                        if (isset($configItem['min']) && is_numeric($datum) && $configItem['min'] > $datum) {
                            $this->paramsException($configItem['des'] ?? $configItem['desc'] ?? $key, $key, 3, $configItem['min']);
                        }
                        if (isset($configItem['max']) && is_numeric($datum) && $configItem['max'] < $datum) {
                            $this->paramsException($configItem['des'] ?? $configItem['desc'] ?? $key, $key, 4, $configItem['max']);
                        }
                        break;
                }
            }
            $realData[$key] = $datum;
        }
        return $realData;
    }

    private function params(array $validate): void
    {
        $paramsDta = $this->request->params();
        $this->realParams = $this->vParams($validate, $paramsDta);
    }

    private function paramsException($desc, $key, $type, string $val = "")
    {
        $message = "";
        switch ($type) {
            case 1:
                $message = "为必填项";
                break;
            case 2:
                $message = "数据类型不正确";
                break;
            case 3:
                $message = "不能小于";
                break;
            case 4:
                $message = "不能大于";
                break;
            case 5:
                $message = "必须为";
                break;
        }
        $message = $message . $val;
        throw new RequestParamsException($desc . $message, $type, $key, $desc, $val);
    }

    public function callback(ContainerInterface &$server): void
    {
        $server->put(SafeParamsInject::class, new SafeParams($this->realParams));
    }
}