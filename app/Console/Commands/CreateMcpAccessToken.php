<?php

namespace App\Console\Commands;

use App\Mcp\Auth\McpTokenService;
use App\Models\Event;
use Illuminate\Console\Command;

class CreateMcpAccessToken extends Command
{
    protected $signature = 'mcp:token:create
        {event_id : Event ID to scope this token to}
        {--name=n8n : Human-readable integration name}
        {--ability=* : Allowed ability; repeat for multiple abilities}
        {--expires= : Optional lifetime in days}';

    protected $description = 'Create an event-scoped bearer token for the MCP server';

    public function handle(McpTokenService $tokens): int
    {
        $event = Event::query()->find($this->argument('event_id'));

        if (! $event) {
            $this->error('Event not found.');

            return self::FAILURE;
        }

        $abilities = array_values(array_filter($this->option('ability')));
        $result = $tokens->issue(
            eventId: (int) $event->id,
            organizationId: (int) $event->organization_id,
            name: (string) $this->option('name'),
            abilities: $abilities ?: ['registrations.read'],
            expiresInDays: $this->option('expires') ? (int) $this->option('expires') : null,
        );

        $this->info('MCP access token created. Copy it now; it will not be shown again.');
        $this->line($result['plain_text_token']);
        $this->newLine();
        $this->line("Event: {$event->id} | Organization: {$event->organization_id}");
        $this->line('Abilities: '.implode(', ', $result['token']->abilities));

        return self::SUCCESS;
    }
}
