<?php

namespace App\Submissions\Services;

use App\Models\Event;
use App\Services\ProviderManager;
use App\Submissions\Models\PortalLoginToken;
use App\Submissions\Models\PortalUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PortalAuthenticationService
{
    public const TOKEN_TTL_MINUTES = 20;

    public function __construct(private ProviderManager $providers) {}

    public function sendMagicLink(string $email, string $role, Event $event): void
    {
        $email = mb_strtolower(trim($email));
        $user = PortalUser::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $email, 'roles' => [$role], 'status' => 'active'],
        );
        $roles = array_values(array_unique([...($user->roles ?? []), $role]));
        $user->forceFill(['roles' => $roles])->save();

        $plain = Str::random(64);
        $user->loginTokens()->create([
            'type' => 'magic_link',
            'token_hash' => hash('sha256', $plain),
            'expires_at' => now()->addMinutes(self::TOKEN_TTL_MINUTES),
        ]);
        $url = route('submissions.portal.consume', ['token' => $plain]);
        $subject = "Your secure link for {$event->title}";
        $html = view('submissions.emails.magic-link', compact('event', 'url'))->render();

        $this->providers->getProvider()->send($email, $subject, $html, "Open your secure link: {$url}");
    }

    public function consume(string $plain): PortalUser
    {
        $hash = hash('sha256', $plain);

        return DB::transaction(function () use ($hash): PortalUser {
            /** @var PortalLoginToken|null $token */
            $token = PortalLoginToken::query()->where('token_hash', $hash)->lockForUpdate()->first();
            if (! $token || $token->used_at || $token->expires_at->isPast()) {
                throw ValidationException::withMessages(['token' => 'This sign-in link is invalid or expired.']);
            }
            $token->forceFill(['used_at' => now()])->save();
            $token->user->forceFill(['email_verified_at' => now(), 'last_login_at' => now()])->save();

            return $token->user;
        });
    }
}
