pmsphp 1.0
===============
一个新型php api 应用框架

# 特性
* 基于PHP `8.0+`开发
* `swoole` 服务基于 `5.0.0+`
* 数据库操作 基于 `think-orm` `3.0` 版本
* 支持 `swoole` Mysql 连接池
* 支持 `swoole` Redis 连接池

> 框架允许环境要求 `php` `8.1.0+` 

# 文档
还在编写中，敬请期待...

# 安装
```bash
composer create-project superpms/pms pms
```

# 启动 HTTP 服务

## 以 `swoole` 模式启动 `http` 服务
### windows 环境启动
[需要使用 `swoole` 官方提供的 CygWin `swoole-cli` 运行](https://www.swoole.com/download) 如 `swoole-cli-v5.0.3-cygwin-x64.zip` 版本
```bash
cd pms
swoole-cli http.php
```
或使用
```bash
cd pms
composer run dev:win:http-swoole
```
---

### linux\mac 环境启动
[需要安装 对应`php` 版本的 `swoole 5.0+` 扩展](https://pecl.php.net/package/swoole)
```bash
cd pms
php http.php
```
或使用
```bash
cd pms
composer run dev:linux:http-swoole
```
或
```bash
cd pms
composer run dev:mac:http-swoole
```


## 以 `php` 内置web服务器 启动 `http` 服务
```bash
cd pms
php -S 0.0.0.0:8080 -t public
```
或使用
```bash
cd pms
composer run dev:http
```
---
> 以上启动方式，仅限于 `dev` 环境，在生产环境，请使用 `nginx` 或 `apache` 等 `web` 服务器进行配置或反向代理

# 其他安装
## `php redis`扩展: （选择PHP对应版本进行下载安装）
### 1、[windows 环境](https://windows.php.net/downloads/pecl/releases/redis/)
### 2、[linux\mac 环境](https://pecl.php.net/package/redis)

## `redis` 服务器 (选择适合的版本)
### 1、[windows 环境](https://github.com/MicrosoftArchive/redis/releases)
### 2、[linux\mac 环境](https://redis.io/download)


## 命名规范

`PmsPHP`遵循PSR-2命名规范和PSR-4自动加载规范。

# 参与开发
直接提交PR或者Issue即可

# 版权信息

PmsPHP遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2023-2024 by PmsPHP (http://pmsphp.cn) All rights reserved。

更多细节参阅 [LICENSE.txt](LICENSE.txt)