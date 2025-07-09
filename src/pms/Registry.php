<?php

namespace pms;

use pms\facade\Db;

abstract class Registry
{

    protected static string $version = "1.0.0";

    protected static string $modelClass;
    protected static string $defaultCreateParentKey = "ROOT";

    /**
     * 使用 AES-256-CBC 加密字符串
     * @param string $data 要加密的字符串
     * @param string $password 加密密钥
     * @return string 返回 base64 编码的加密结果
     */
    protected static function encrypt(string $data, string $password): string
    {
        $iv = openssl_random_pseudo_bytes(16); // 生成随机初始化向量
        $encrypted = openssl_encrypt(
            $data,
            'aes-256-cbc',
            hash('sha256', $password, true), // 使用 SHA-256 哈希密钥
            OPENSSL_RAW_DATA,
            $iv
        );
        return base64_encode($iv . $encrypted);
    }

    /**
     * 解密字符串
     * @param string $data base64 编码的加密字符串
     * @param string $password 解密密钥
     * @return string|false 返回解密后的原始字符串，失败返回 false
     */
    protected static function decrypt(string $data, string $password): bool|string{
        $data = base64_decode($data);
        $iv = substr($data, 0, 16); // 提取前16位作为 IV
        $encrypted = substr($data, 16);
        return openssl_decrypt(
            $encrypted,
            'aes-256-cbc',
            hash('sha256', $password, true),
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    protected static function getDefaultParentKey($parentKey=null)
    {
        if ($parentKey === null) {
            $parentKey = static::$defaultCreateParentKey;
        }
        return $parentKey;
    }

    protected static function useModel(): Model|\think\Model{
        $name = static::$modelClass;
        return new $name();
    }

    protected static function convertType(mixed $value): string
    {
        $type = gettype($value);
        if ($type == 'array') {
            return "JSONARRAY";
        }
        return strtoupper($type);
    }

    protected static function setConvertValue(mixed $value)
    {
        $type = gettype($value);
        if ($type == 'array') {
            return json_encode($value);
        } else if ($type == 'object') {
            if (method_exists($value, 'toArray')) {
                return json_encode($value->toArray());
            }
            return json_encode($value);
        } else if ($type == 'boolean') {
            return (int)$value;
        } else {
            return $value;
        }
    }

    protected static function getConvertValue(string $type, mixed $value)
    {
        if (empty($type)) {
            return $value;
        }
        switch (strtoupper($type)) {
            case 'BOOL':
            case 'BOOLEAN':
                return (boolean)$value;
            case 'JSONARRAY':
                if (is_string($value)) {
                    $value = json_decode($value, true);
                }
                break;
            case "INTEGER":
                $value = intval($value);
                break;
            case "DOUBLE":
                $value = floatval($value);
                break;
            case "STRING":
                $value = $value . '';
                break;
            case 'NULL':
                $value = null;
                break;
            default:
                break;
        }
        return $value;
    }

    protected static function findAllChildGenealogy(array $data, string $key, string $parentKey, mixed $currentParentValue, string $resultKey): array
    {
        $vals = [];
        foreach ($data as $item) {
            if ($item[$parentKey] === $currentParentValue) {
                $vals = [
                    ...$vals,
                    $item[$resultKey],
                    ...static::findAllChildGenealogy($data, $key, $parentKey, $item[$key], $resultKey)
                ];
            }
        }
        return $vals;
    }


    /**
     * 获取配置
     * @param string $key 配置键名
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        $value = static::gets([$key]);
        return $value[$key] ?? null;
    }


    /**
     * 获取多个配置
     * @param array $keys 配置项key集合，数组成员支持以下三种格式：
     *  '配置名称'、
     *  '配置名称'=>'别名'、
     *  '配置名称'=>&$configItem
     * @return array
     */
    public static function gets(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }
        $realKeys = [];
        $alias = [];
        $directlyUnder = [];

        foreach ($keys as $index => $value) {
            if (is_string($index)) {
                $realKeys[] = $index;
                if (is_string($value)) {
                    $alias[$index] = $value;
                } else {
                    $directlyUnder[] = $index;
                }
            } else {
                $realKeys[] = $value;
            }
        }

        $model = static::useModel();
        $configList = $model::where([
            ['key', 'in', $realKeys]
        ])->select()->toArray();

        $realConfig = [];
        foreach ($configList as $config) {
            $realConfig[$config['key']] = static::getConvertValue($config['type'], $config['value']);
        }

        foreach ($realKeys as $key) {
            if (!isset($realConfig[$key])) {
                $realConfig[$key] = null;
            }
        }

        $returnConfig = [];
        foreach ($realConfig as $key => $value) {
            if (in_array($key, $directlyUnder)) {
                $keys[$key] = $value;
            }
            if (isset($alias[$key])) {
                $returnConfig[$alias[$key]] = $value;
            } else {
                $returnConfig[$key] = $value;
            }
        }
        unset($realConfig);
        unset($realKeys);
        unset($alias);
        unset($data);
        unset($directlyUnder);
        return $returnConfig;
    }

    /**
     * 通过父配置项获取所有子代配置
     * @param string $parent 父配置项key
     * @return array
     */
    public static function getP(string $parent): array{
        $model = static::useModel();
        $configList = $model::where([
            'parent' => $parent,
        ])->select()->toArray();
        $realConfig = [];
        foreach ($configList as $config) {
            $realConfig[$config['key']] = static::getConvertValue($config['type'], $config['value']);
        }
        return $realConfig;
    }


    /**
     * 设置配置项(存在时返回false)
     * @param string $key 配置项
     * @param mixed $value 配置值
     * @param string $name 配置名称
     * @param string|null $parentKey 父级配置项
     * @return bool
     */
    public static function set(string $key, mixed $value, string $name,string $parentKey= null): bool{
        try{
            $parentKey = static::getDefaultParentKey($parentKey);
            static::useModel()->insert([
                'parent'=>$parentKey,
                'name'=>$name,
                'key'=>$key,
                'type'=>static::convertType($value),
            ]);
            return true;
        }catch (\Throwable $e){
            return false;
        }
    }

    /**
     * 修改指定key的配置 (不存在则返回false)
     * @param string $key 配置键名
     * @param mixed $value 配置值
     * @return bool
     */
    public static function update(string $key, mixed $value): bool{
        $saveData = [
            'value' => static::setConvertValue($value),
            'type' => static::convertType($value),
        ];
        return static::useModel()::where([
            ['key', '=', $key]
        ])->save($saveData);
    }

    /**
     * 通过父节点修改建指定key 的配置
     * @param string $parentNode 父配置键名
     * @param array $data 配置数据[key=>value,...]
     * @return bool
     */
    public static function updateP(string $parentNode, array $data): bool{
        if (!static::has($parentNode)) {
            return false;
        }
        $nodeList = static::useModel()::where([
            'parent' => $parentNode,
        ])->select()->toArray();
        $value = [];
        foreach ($nodeList as $node) {
            if (isset($data[$node['key']])) {
                $value[] = [
                    'id' => $node['id'],
                    'value' => static::setConvertValue($data[$node['key']]),
                    'type' => static::convertType($data[$node['key']]),
                ];
            }
        }
        $needCount = count($value);
        if ($needCount === 0) {
            return true;
        }
        Db::startTrans();
        try {
            /**
             * @var Model|\think\Model $m
             */
            $m = static::useModel();
            $result = $m->saveAll($value);
            if (count($result) !== $needCount) {
                Db::rollback();
                return false;
            }
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }


    /**
     * 保存配置(如果不存在则创建)
     * @param string $key 配置键名
     * @param mixed $value 配置值
     * @param string|null $name 配置名称
     * @param string|null $parentKey 父配置键名
     * @return bool
     */
    public static function save(string $key, mixed $value, string $name = null,string $parentKey = null): bool{
        if (static::has($key)) {
            return static::update($key, $value);
        } else {
            return static::set($key, $value, $name, $parentKey);
        }
    }


    /**
     * 批量保存配置 (不存在则创建)
     * @param array $data 配置数据[[key=>value,...],...]
     * @return bool
     */
    public static function saveAll(array $data): bool
    {
        $nodeList = static::useModel()::where([
            ['key','in',array_column($data,'key')]
        ])->select()->toArray();
        $value = [];
        foreach ($data as $key => $item){
            $parentKey = static::getDefaultParentKey($item['parent'] ?? null);
            $val = [
                'key'=>strtoupper($item['key']),
                'value' => static::setConvertValue($item['value'] ?? null),
                'type' => static::convertType($item['value']??null),
                'parent' => $parentKey,
            ];
            if(array_key_exists('name',$item)){
                $val['name'] = $item['name'];
            }
            if(array_key_exists('description',$item)){
                $val['description'] = $item['description'];
            }
            foreach ($nodeList as $node){
                if ($node['key'] == $key) {
                    $val['id'] = $node['id'];
                }
            }
            $value[] = $val;
        }
        Db::startTrans();
        try {
            /**
             * @var Model|\think\Model $m
             */
            $m = static::useModel();
            $result = $m->saveAll($value);
            if (count($result) !== count($value)) {
                Db::rollback();
                return false;
            }
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

    }

    /**
     * 通过父节点批量保存配置(不存在则创建)
     * @param string $parentKey 父配置键
     * @param array $data 配置数据[ [key=>value,name=>''],...]
     * @return bool
     */
    public static function saveP(string $parentKey, array $data): bool{
        if (static::has($parentKey)) {
            return false;
        }
        if(empty($data)){
            return true;
        }
        $nodeList = static::useModel()::where([
            'parent' => $parentKey,
        ])->select()->toArray();
        $value = [];
        foreach ($data as $key => $item){
            $val = [
                'key'=>strtoupper($item['key']),
                'value' => static::setConvertValue($item['value'] ?? null),
                'type' => static::convertType($item['value']??null),
                'parent' => $parentKey,
            ];
            if(array_key_exists('name',$item)){
                $val['name'] = $item['name'];
            }
            if(array_key_exists('description',$item)){
                $val['description'] = $item['description'];
            }
            foreach ($nodeList as $node){
                if ($node['key'] == $key) {
                    $val['id'] = $node['id'];
                }
            }
            $value[] = $val;
        }
        Db::startTrans();
        try {
            /**
             * @var Model|\think\Model $m
             */
            $m = static::useModel();
            $result = $m->saveAll($value);
            if (count($result) !== count($value)) {
                Db::rollback();
                return false;
            }
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }



    /**
     * 配置是否存在
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return static::have([$key]);
    }

    /**
     * 多个配置是否存在
     * @param string[] $keys 配置项
     * @return bool
     */
    public static function have(array $keys): bool
    {
        return static::haveCount($keys)=== count($keys);
    }

    /**
     * 获取存在配置项数量
     * @param string[] $keys
     * @return int
     */
    public static function haveCount(array $keys): int
    {
        return static::useModel()::where([
            ['key', 'in', $keys]
        ])->count();
    }


    /**
     * 删除配置项
     * @param string $key
     * @return bool
     */
    public static function delete(string $key): bool
    {
        return static::useModel()->where([
            ['key','=>',$key]
        ])->delete();
    }

    /**
     * 删除配置项(包含所有子集配置)
     * @param string $key 起始配置项
     * @return bool
     */
    public static function deleteP(string $key): bool
    {
        $data = static::useModel()->select()->toArray();
        $keys = static::findAllChildGenealogy($data, 'key', 'parent', $key, 'key');
        $keys[] = $key;
        return static::useModel()->where([
            ['key','in',$keys]
        ])->delete();
    }


    /**
     * 备份注册表到文件
     * @param string $backPath 文件路径
     * @param string|null $password 密码
     * @return bool
     */
    public static function backup(string $backPath,string $password=null): bool
    {
        $path = pathinfo($backPath)['dirname'];
        if (!is_dir($path) && !file_exists($path)) {
            @mkdir($path, 0777, true);
        }
        $file = fopen($backPath, 'w');
        if (!$file) {
            return false;
        }
        $data = static::generateBackupStr($password);
        fwrite($file, $data);
        fclose($file);
        return true;
    }

    /**
     * 生成注册表备份数据
     * @param string|null $password 密码
     * @return string
     */
    public static function generateBackupStr(string $password=null): string
    {
        $data = static::useModel()::select()->toArray();
        $data = [
            'registry' => static::$version,
            'data' => $data
        ];
        $data = json_encode($data,320);
        if($password === null){
            return $data;
        }
        return static::encrypt($data,$password);
    }

    /**
     * 格式化注册表
     * @return bool
     */
    public static function format(): bool
    {
        $model = static::useModel();
        $table = $model->getTable();
        $model->getConnection()->execute("truncate $table");
        return true;
    }

    /**
     * 使用文件还原注册表
     * @param string $filePath 文件地址
     * @param string|null $password 密码
     * @return bool
     */
    public static function restore (string $filePath,string $password=null): bool{
        $data = file_get_contents($filePath);
        if (!$data) {
            return false;
        }
        $data = static::unpackBackupStr($data, $password);
        if($data === false){
            return false;
        }
        $data = json_decode($data,true);
        if($data === null){
            return false;
        }
        $data = $data['data'] ?? [];
        $saveData = [];
        foreach ($data as $k=>$v){
            if(isset($v['key']) && isset($v['parent'])){
                $saveData[] = [
                    'parent'=>$v['parent'],
                    'name'=>$v['name'],
                    'key'=>$v['key'],
                    'type'=>$v['type'],
                    'value'=>$v['value'],
                    'description'=>$v['description'],
                ];
            }
        }

        Db::startTrans();
        try {
            static::useModel()::where([
                ['key','in',array_column($data,'key')]
            ])->delete();
            $result = static::useModel()->saveAll($saveData);
            if (count($result) !== count($saveData)) {
                Db::rollback();
                return false;
            }
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }


    /**
     * 解压注册表备份文件的内容(密码错误或还原失败时返回false)
     * @param string $data 文件内容字符串
     * @param string|null $password 密码
     * @return string|false
     */
    public static function unpackBackupStr(string $data, string $password=null):string|false{
        if($password === null){
            return $data;
        }
        return static::decrypt($data,$password);
    }

}