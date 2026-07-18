<?php

namespace App\Mcp\Tools;

use App\Mcp\Auth\McpAccessContext;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Throwable;

abstract class ReadOnlyEventTool extends Tool
{
    final public function handle(Request $request): Response|ResponseFactory
    {
        $startedAt = hrtime(true);
        $status = 'success';
        $context = app(McpAccessContext::class);

        if (! $context->authorize($this->requiredAbility())) {
            $status = 'denied';
            $this->audit($context, $status, $startedAt);

            return Response::error('This token is not authorized to use this tool.');
        }

        try {
            return $this->execute($request);
        } catch (Throwable $exception) {
            $status = 'failed';
            throw $exception;
        } finally {
            $this->audit($context, $status, $startedAt);
        }
    }

    abstract protected function execute(Request $request): Response|ResponseFactory;

    abstract protected function toolName(): string;

    protected function requiredAbility(): string
    {
        return 'registrations.read';
    }

    private function audit(McpAccessContext $context, string $status, int $startedAt): void
    {
        DB::table('mcp_tool_audit_logs')->insert([
            'mcp_access_token_id' => $context->token->id,
            'event_id' => $context->token->event_id,
            'org_id' => $context->token->org_id,
            'tool' => $this->toolName(),
            'status' => $status,
            'duration_ms' => max(0, (int) round((hrtime(true) - $startedAt) / 1_000_000)),
            'request_fingerprint' => request()->header('X-Request-ID'),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
