<?php

namespace App\Http\Middleware;

use App\Mcp\Auth\McpAccessContext;
use App\Mcp\Auth\McpAccessToken;
use App\Shared\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMcpToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = $request->bearerToken();

        if (! is_string($plainTextToken) || $plainTextToken === '') {
            return $this->unauthorized();
        }

        $token = McpAccessToken::query()
            ->where('token_hash', hash('sha256', $plainTextToken))
            ->first();

        if (! $token?->isUsable()) {
            return $this->unauthorized();
        }

        $tenant = new TenantContext((int) $token->event_id, (int) $token->org_id);
        config([
            'event.event_id' => $tenant->eventId,
            'event.org_id' => $tenant->organizationId,
        ]);

        app()->instance(TenantContext::class, $tenant);
        app()->instance(McpAccessContext::class, new McpAccessContext($token));

        $token->forceFill(['last_used_at' => now()])->saveQuietly();

        return $next($request);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json(['message' => 'Invalid or expired MCP access token.'], 401);
    }
}
