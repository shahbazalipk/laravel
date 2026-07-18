<?php

return [
    'event_operations' => [
        'path' => env('MCP_EVENT_OPERATIONS_PATH', '/mcp/event-operations'),
        'rate_limit_per_minute' => (int) env('MCP_RATE_LIMIT_PER_MINUTE', 60),
    ],
];
