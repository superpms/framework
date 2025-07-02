<?php

namespace pms\app\handler;
use Swoole\WebSocket\Server;
use Swoole\WebSocket\Frame;
use Swoole\Http\Request;
/**
 * @method message(Server $server, Frame $frame);
 * @method open(Server $server, Request $request);
 * @method disconnect(Server $server, int $fd)
 * @method close(Server $server, int $fd, int $reactorId);
 */
interface SwooleWebSocketHandler{


}