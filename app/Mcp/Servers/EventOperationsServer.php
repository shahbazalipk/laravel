<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CheckInSummaryTool;
use App\Mcp\Tools\PaymentSummaryTool;
use App\Mcp\Tools\RecentRegistrationsTool;
use App\Mcp\Tools\RegistrationsSummaryTool;
use App\Mcp\Tools\RegistrationStatusTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Event Operations Server')]
#[Version('1.0.0')]
#[Instructions('Read-only, event-scoped operational data. Registration tools include website profile fields (first name, last name, photo, job title, company, and LinkedIn from existing custom answers) but never email or phone. Use exact registration numbers for individual lookups. Never infer access to another event.')]
class EventOperationsServer extends Server
{
    protected array $tools = [
        RegistrationsSummaryTool::class,
        RegistrationStatusTool::class,
        PaymentSummaryTool::class,
        RecentRegistrationsTool::class,
        CheckInSummaryTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
