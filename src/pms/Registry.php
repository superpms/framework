<?php

namespace pms;

use pms\facade\Db;

abstract class Registry
{
    protected string $version = "1.0.1";
    protected string $modelClass;
    protected string $defaultCreateParentKey = "ROOT";
    protected array $andWhere = [];

    /**
     * @var self|null
     */
    private static Registry|null $self = null;

    /**
     * 获取单例
     * @return self
     */
    public static function instance(){
        if (static::$self == null) {
            static::$self = new static();
        }
        return static::$self;
    }

    /**
     * instance 方法别名
     * @return self
     */
    public static function inst(){
        return static::instance();
    }

    /**
     * 使用 AES-256-CBC 加密字符串
     * @param string $data 要加密的字符串
     * @param string $password 加密密钥
     * @return string 返回 base64 编码的加密结果
     */
    protected function encrypt(string $data, string $password): string
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
    protected function decrypt(string $data, string $password): bool|string{
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

    protected function getDefaultParentKey($parentKey=null)
    {
        if ($parentKey === null) {
            $parentKey = $this->defaultCreateParentKey;
        }
        return $parentKey;
    }

    protected function useModel(): Model|\think\Model{
        $name = $this->modelClass;
        return new $name();
    }

    protected function convertType(mixed $value): string
    {
        $type = gettype($value);
        if ($type == 'array') {
            return "JSONARRAY";
        }
        return strtoupper($type);
    }

    protected function setConvertValue(mixed $value)
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

    protected function getConvertValue(string $type, mixed $value)
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

    protected function findAllChildGenealogy(array $data, string $key, string $parentKey, mixed $currentParentValue, string $resultKey): array
    {
        $vals = [];
        foreach ($data as $item) {
            if ($item[$parentKey] === $currentParentValue) {
                $vals = [
                    ...$vals,
                    $item[$resultKey],
                    ...$this->findAllChildGenealogy($data, $key, $parentKey, $item[$key], $resultKey)
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
    public function get(string $key): mixed
    {
        $value = $this->gets([$key]);
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
    public function gets(array $keys): array
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

        $model = $this->useModel();
        $configList = $model::where([
            ['key', 'in', $realKeys],
            ...$this->andWhere
        ])->select()->toArray();

        $realConfig = [];
        foreach ($configList as $config) {
            $realConfig[$config['key']] = $this->getConvertValue($config['type'], $config['value']);
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
    public function getP(string $parent): array{
        $model = $this->useModel();
        $configList = $model::where([
            ['parent','=',$parent],
            ...$this->andWhere
        ])->select()->toArray();
        $realConfig = [];
        foreach ($configList as $config) {
            $realConfig[$config['key']] = $this->getConvertValue($config['type'], $config['value']);
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
    public function set(string $key, mixed $value, string $name,string $parentKey= null): bool{
        try{
            $parentKey = $this->getDefaultParentKey($parentKey);
            $this->useModel()->insert([
                'parent'=>$parentKey,
                'name'=>$name,
                'key'=>$key,
                'type'=>$this->convertType($value),
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
    public function update(string $key, mixed $value): bool{
        $saveData = [
            'value' => $this->setConvertValue($value),
            'type' => $this->convertType($value),
        ];
        return $this->useModel()::where([
            ['key', '=', $key],
            ...$this->andWhere
        ])->save($saveData);
    }

    /**
     * 通过父节点修改建指定key 的配置
     * @param string $parentNode 父配置键名
     * @param array $data 配置数据[key=>value,...]
     * @return bool
     */
    public function updateP(string $parentNode, array $data): bool{
        if (!$this->has($parentNode)) {
            return false;
        }
        $nodeList = $this->useModel()::where([
            ['parent','=',$parentNode,],
            ...$this->andWhere
        ])->select()->toArray();
        $value = [];
        foreach ($nodeList as $node) {
            if (isset($data[$node['key']])) {
                $value[] = [
                    'id' => $node['id'],
                    'value' => $this->setConvertValue($data[$node['key']]),
                    'type' => $this->convertType($data[$node['key']]),
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
            $m = $this->useModel();
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
    public function save(string $key, mixed $value, string $name = null,string $parentKey = null): bool{
        if ($this->has($key)) {
            return $this->update($key, $value);
        } else {
            return $this->set($key, $value, $name, $parentKey);
        }
    }


    /**
     * 批量保存配置 (不存在则创建)
     * @param array $data 配置数据[[key=>value,...],...]
     * @return bool
     */
    public function saveAll(array $data): bool
    {
        $nodeList = $this->useModel()::where([
            ['key','in',array_column($data,'key')],
            ...$this->andWhere
        ])->select()->toArray();
        $value = [];
        foreach ($data as $key => $item){
            $parentKey = $this->getDefaultParentKey($item['parent'] ?? null);
            $val = [
                'key'=>strtoupper($item['key']),
                'value' => $this->setConvertValue($item['value'] ?? null),
                'type' => $this->convertType($item['value']??null),
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
            $m = $this->useModel();
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
    public function saveP(string $parentKey, array $data): bool{
        if ($this->has($parentKey)) {
            return false;
        }
        if(empty($data)){
            return true;
        }
        $nodeList = $this->useModel()::where([
            ['parent','=',$parentKey],
            ...$this->andWhere
        ])->select()->toArray();
        $value = [];
        foreach ($data as $key => $item){
            $val = [
                'key'=>strtoupper($item['key']),
                'value' => $this->setConvertValue($item['value'] ?? null),
                'type' => $this->convertType($item['value']??null),
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
            $m = $this->useModel();
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
    public function has(string $key): bool
    {
        return $this->have([$key]);
    }

    /**
     * 多个配置是否存在
     * @param string[] $keys 配置项
     * @return bool
     */
    public function have(array $keys): bool
    {
        return $this->haveCount($keys)=== count($keys);
    }

    /**
     * 获取存在配置项数量
     * @param string[] $keys
     * @return int
     */
    public function haveCount(array $keys): int
    {
        return $this->useModel()::where([
            ['key', 'in', $keys],
            ...$this->andWhere
        ])->count();
    }


    /**
     * 删除配置项
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->useModel()->where([
            ['key','=>',$key],
            ...$this->andWhere
        ])->delete();
    }

    /**
     * 删除配置项(包含所有子集配置)
     * @param string $key 起始配置项
     * @return bool
     */
    public function deleteP(string $key): bool
    {
        $data = $this->useModel()->select()->toArray();
        $keys = $this->findAllChildGenealogy($data, 'key', 'parent', $key, 'key');
        $keys[] = $key;
        return $this->useModel()->where([
            ['key','in',$keys],
            ...$this->andWhere
        ])->delete();
    }


    /**
     * 备份注册表到文件
     * @param string $backPath 文件路径
     * @param string|null $password 密码
     * @return bool
     */
    public function backup(string $backPath,string $password=null): bool
    {
        $path = pathinfo($backPath)['dirname'];
        if (!is_dir($path) && !file_exists($path)) {
            @mkdir($path, 0777, true);
        }
        $file = fopen($backPath, 'w');
        if (!$file) {
            return false;
        }
        $data = $this->generateBackupStr($password);
        fwrite($file, $data);
        fclose($file);
        return true;
    }

    /**
     * 生成注册表备份数据
     * @param string|null $password 密码
     * @return string
     */
    public function generateBackupStr(string $password=null): string
    {
        $data = $this->useModel()::select()->toArray();
        $data = [
            'registry' => $this->version,
            'data' => $data
        ];
        $data = json_encode($data,320);
        if($password === null){
            return $data;
        }
        return $this->encrypt($data,$password);
    }

    /**
     * 格式化注册表
     * @return bool
     */
    public function format(): bool
    {
        $model = $this->useModel();
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
    public function restore (string $filePath,string $password=null): bool{
        $data = file_get_contents($filePath);
        if (!$data) {
            return false;
        }
        $data = $this->unpackBackupStr($data, $password);
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
            $this->useModel()::where([
                ['key','in',array_column($data,'key')],
                ...$this->andWhere
            ])->delete();
            $result = $this->useModel()->saveAll($saveData);
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
    public function unpackBackupStr(string $data, string $password=null):string|false{
        if($password === null){
            return $data;
        }
        return $this->decrypt($data,$password);
    }

}