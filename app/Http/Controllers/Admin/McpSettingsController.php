<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMcpAccessTokenRequest;
use App\Mcp\Auth\McpAccessToken;
use App\Mcp\Auth\McpTokenService;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class McpSettingsController extends Controller
{
    public function index(): View
    {
        $event = $this->currentEvent();

        $tokens = McpAccessToken::query()
            ->where('event_id', $event->id)
            ->where('org_id', $event->organization_id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.mcp.index', [
            'event' => $event,
            'mcpUrl' => url(config('mcp.event_operations.path', '/mcp/event-operations')),
            'tokens' => $tokens,
        ]);
    }

    public function store(StoreMcpAccessTokenRequest $request, McpTokenService $tokens): RedirectResponse
    {
        $event = $this->currentEvent();
        $validated = $request->validated();

        $result = $tokens->issue(
            eventId: (int) $event->id,
            organizationId: (int) $event->organization_id,
            name: $validated['name'],
            abilities: $validated['abilities'] ?? ['registrations.read'],
            expiresInDays: isset($validated['expires_in_days'])
                ? (int) $validated['expires_in_days']
                : null,
        );

        return redirect()
            ->route('admin.mcp.index')
            ->with('success', 'MCP access token created. Copy it now — it will not be shown again.')
            ->with('mcp_plain_text_token', $result['plain_text_token']);
    }

    public function revoke(McpAccessToken $token, McpTokenService $tokens): RedirectResponse
    {
        $event = $this->currentEvent();

        abort_unless(
            (int) $token->event_id === (int) $event->id
            && (int) $token->org_id === (int) $event->organization_id,
            404
        );

        if ($token->revoked_at === null) {
            $tokens->revoke($token);
        }

        return redirect()
            ->route('admin.mcp.index')
            ->with('success', "Token “{$token->name}” was revoked.");
    }

    private function currentEvent(): Event
    {
        $event = Event::getCurrentEvent();
        abort_unless($event, 404);

        return $event;
    }
}
