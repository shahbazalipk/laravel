<?php

namespace App\Mcp\Auth;

use Illuminate\Support\Str;

class McpTokenService
{
    /**
     * @param  list<string>  $abilities
     * @return array{token: McpAccessToken, plain_text_token: string}
     */
    public function issue(
        int $eventId,
        int $organizationId,
        string $name,
        array $abilities = ['registrations.read'],
        ?int $expiresInDays = null,
    ): array {
        $plainTextToken = 'evt_mcp_'.Str::random(64);

        $token = McpAccessToken::query()->create([
            'event_id' => $eventId,
            'org_id' => $organizationId,
            'name' => $name,
            'token_hash' => hash('sha256', $plainTextToken),
            'abilities' => array_values(array_unique($abilities)),
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);

        return ['token' => $token, 'plain_text_token' => $plainTextToken];
    }

    public function revoke(McpAccessToken $token): void
    {
        $token->forceFill(['revoked_at' => now()])->save();
    }
}
