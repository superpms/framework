<?php

namespace pms\app\handler;

use Swoole\Server;


/**
 * @method connect(Server $server, int $fd, int $reactorId);
 * @method receive(Server $server, int $fd, int $reactorId, string $data);
 * @method close(Server $server, int $fd, int $reactorId);
 */
interface SwooleTcpHandler{

}