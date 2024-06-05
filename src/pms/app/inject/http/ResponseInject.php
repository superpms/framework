<?php

namespace pms\app\inject\http;

interface ResponseInject
{
    /**
     * 判断 Response 对象是否已结束 (end) 或已分离 (detach)。
     * @return bool
     */
    public function isWritable():bool;

    /**
     * 设置 HTTP 响应的 cookie 信息。别名 setCookie()。此方法参数与 PHP 的 setcookie 一致。
     * @param string $name Cookie 的 Key
     * @param string $value Cookie 的 value
     * @param int $expires Cookie 的过期时间
     * @param string $path 规定 Cookie 的服务器路径。
     * @param string $domain 规定 Cookie 的域名
     * @param bool $secure 规定是否通过安全的 HTTPS 连接来传输 Cookie
     * @param bool $httponly 是否允许浏览器的JavaScript访问带有 HttpOnly 属性的 Cookie，true 表示不允许，false 表示允许
     * @param string $samesite 限制第三方 Cookie，从而减少安全风险，可选值为 Strict，Lax，None
     * @param string $priority Cookie优先级，当Cookie数量超过规定，低优先级的会先被删除，可选值为 Low，Medium，High
     * @return bool
     */
    public function cookie(string $name, string $value = '', int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = '', string $priority = ''): bool;

    /**
     * 设置 HTTP 响应的 cookie 信息。为 cookie() 的别名。此方法参数与 PHP 的 setcookie 一致。
     * @param string $name Cookie 的 Key
     * @param string $value Cookie 的 value
     * @param int $expires Cookie 的过期时间
     * @param string $path 规定 Cookie 的服务器路径。
     * @param string $domain 规定 Cookie 的域名
     * @param bool $secure 规定是否通过安全的 HTTPS 连接来传输 Cookie
     * @param bool $httponly 是否允许浏览器的JavaScript访问带有 HttpOnly 属性的 Cookie，true 表示不允许，false 表示允许
     * @param string $samesite 限制第三方 Cookie，从而减少安全风险，可选值为 Strict，Lax，None
     * @param string $priority Cookie优先级，当Cookie数量超过规定，低优先级的会先被删除，可选值为 Low，Medium，High
     * @return bool
     */
    public function setCookie(string $name, string $value = '', int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = '', string $priority = ''): bool;

    /**
     * 设置 HTTP 响应的 cookie 信息。并使用 rawurlencode 编码 value。
     * @param string $name Cookie 的 Key
     * @param string $value Cookie 的 value
     * @param int $expires Cookie 的过期时间
     * @param string $path 规定 Cookie 的服务器路径。
     * @param string $domain 规定 Cookie 的域名
     * @param bool $secure 规定是否通过安全的 HTTPS 连接来传输 Cookie
     * @param bool $httponly 是否允许浏览器的JavaScript访问带有 HttpOnly 属性的 Cookie，true 表示不允许，false 表示允许
     * @param string $samesite 限制第三方 Cookie，从而减少安全风险，可选值为 Strict，Lax，None
     * @param string $priority Cookie优先级，当Cookie数量超过规定，低优先级的会先被删除，可选值为 Low，Medium，High
     * @return bool
     */
    public function rawcookie(string $name, string $value = '', int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = '', string $priority = ''): bool;

    /**
     * 发送 Http 状态码。别名 setStatusCode()
     * @param int $http_code 设置 HttpCode
     * @param string $reason 状态码原因
     * @return bool
     */
    public function status(int $http_code, string $reason = ''): bool;

    /**
     * 发送 Http 状态码。为 status()的别名
     * @param int $http_code 设置 HttpCode
     * @param string $reason 状态码原因
     * @return bool
     */
    public function setStatusCode(int $http_code, string $reason = ''): bool;

    /**
     * 设置 HTTP 响应的 Header 信息,别名 setHeader()
     * @param string $key HTTP 头的 Key
     * @param string|array $value HTTP 头的 value
     * @param bool $format 是否需要对 Key 进行 HTTP 约定格式化【默认 true 会自动格式化】
     * @return bool
     */
    public function header(string $key, string|array $value, bool $format = true): bool;

    /**
     * 设置 HTTP 响应的 Header 信息,别名 setHeader()
     * @param string $key HTTP 头的 Key
     * @param string|array $value HTTP 头的 value
     * @param bool $format 是否需要对 Key 进行 HTTP 约定格式化【默认 true 会自动格式化】
     * @return bool
     */
    public function setHeader(string $key, string|array $value, bool $format = true): bool;

    /**
     * 将 Header 信息附加到 HTTP 响应的末尾，仅在 HTTP2 中可用，用于消息完整性检查，数字签名等。
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function trailer(string $key, string $value): bool;

    /**
     * 启用 Http Chunk 分段向浏览器发送相应内容。关于 Http Chunk 可以参考 Http 协议标准文档。
     * 使用 write 分段发送数据后，end 方法将不接受任何参数，调用 end 只是会发送一个长度为 0 的 Chunk 表示数据传输完毕
     * @param string $content 要发送的数据内容【SwooleHttp请求最大长度不得超过 2M，受 buffer_output_size 配置项控制】
     * @return bool
     */
    public function write(string $content): bool;

    /**
     * 发送 Http 响应体，并结束请求处理。
     * end 只能调用一次，如果需要分多次向客户端发送数据，请使用 write 方法
     *
     * @param string|null $content 要发送的内容【SwooleHttp请求由于受到 output_buffer_size 的限制，默认为 2M，如果大于这个限制则会响应失败，并抛出错误】
     * @return mixed
     */
    public function end(?string $content = null): mixed;

    /**
     * 发送文件到浏览器。
     * @param string $filename 要发送的文件名称【文件不存在或没有访问权限 sendfile 会失败】
     * @param int $offset 上传文件的偏移量【可以指定从文件的中间部分开始传输数据。此特性可用于支持断点续传】
     * @param int $length 发送数据的尺寸,默认：文件的尺寸
     * @return bool
     */
    public function sendfile(string $filename, int $offset = 0, int $length = 0): bool;

    /**
     * 发送 Http 跳转。调用此方法会自动 end 发送并结束响应。
     * @param string $location 跳转的新地址，作为 Location 头进行发送
     * @param int $http_code 状态码【默认为 302 临时跳转，传入 301 表示永久跳转】
     * @return bool
     */
    public function redirect(string $location, int $http_code = 302): bool;

    /**
     * 仅SwooleHttp 有效
     * 分离响应对象。使用此方法后，$response 对象销毁时不会自动 end，与 Http\Response::create 和 Server->send 配合使用。
     * @return bool
     */
    public function detach(): bool;

    /**
     * 仅SwooleHttp 有效
     * 构造新的 Swoole\Http\Response 对象。
     * @param int|array|object $server
     * @param int $fd
     * @return false|self
     */
    public function create(int|array|object $server = -1, int $fd = -1): self|false;

}