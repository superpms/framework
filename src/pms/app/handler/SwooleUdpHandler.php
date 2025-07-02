<?php

namespace pms\app\handler;

use Swoole\Server;

/**
 * @method packet(Server $server, string $data, array $clientInfo);
 * @method receive(Server $server, int $fd, int $reactorId, string $data)
 */
interface SwooleUdpHandler
{


}