<?php

use App\Http\Middleware\AuthenticateMcpToken;
use App\Mcp\Servers\EventOperationsServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web(config('mcp.event_operations.path'), EventOperationsServer::class)
    ->middleware([
        AuthenticateMcpToken::class,
        'throttle:'.config('mcp.event_operations.rate_limit_per_minute').',1',
    ]);
