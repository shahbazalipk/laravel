<?php

namespace App\Registration\Services;

use App\Models\Event;
use App\Models\EventUrl;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RegistrationDraftService
{
    public const COOKIE_NAME = 'registration_resume_token';

    public const DRAFT_TTL_HOURS = 72;

    public function __construct(
        private OnlineRegistrationContext $context
    ) {}

    /**
     * @return array{0: RegistrationDraft, 1: string} Draft and plain resume token
     */
    public function start(Event $event, ?EventUrl $eventUrl, string $email): array
    {
        $email = $this->normalizeEmail($email);
        $plainToken = $this->generatePlainToken();

        $draft = RegistrationDraft::query()->create([
            'event_id' => $event->id,
            'org_id' => $event->organization_id ?? $event->org_id ?? config('event.org_id'),
            'event_url_id' => $eventUrl?->id,
            'email' => $email,
            'resume_token_hash' => $this->hashToken($plainToken),
            'payload' => ['email' => $email],
            'current_step' => RegistrationWizardStep::Email,
            'email_verified_at' => $event->email_verification_required ? null : now(),
            'expires_at' => now()->addHours(self::DRAFT_TTL_HOURS),
        ]);

        return [$draft, $plainToken];
    }

    public function findByPlainToken(?string $plainToken, Event $event): ?RegistrationDraft
    {
        if (!$plainToken) {
            return null;
        }

        return RegistrationDraft::query()
            ->where('event_id', $event->id)
            ->where('resume_token_hash', $this->hashToken($plainToken))
            ->whereNull('completed_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function findByUrlKey(?string $urlKey, Event $event): ?RegistrationDraft
    {
        $publicId = $this->decodeUrlKey($urlKey);
        if (!$publicId) {
            return null;
        }

        return RegistrationDraft::query()
            ->where('event_id', $event->id)
            ->where('public_id', $publicId)
            ->whereNull('completed_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function resolveFromRequest(
        Event $event,
        ?string $cookieToken = null,
        ?string $urlToken = null,
        ?string $urlKey = null
    ): ?RegistrationDraft {
        return $this->findByUrlKey($urlKey, $event)
            ?? $this->findByPlainToken($urlToken, $event)
            ?? $this->findByPlainToken($cookieToken, $event);
    }

    /**
     * Stable URL-safe signed draft key embedded in wizard step URLs.
     */
    public function encodeUrlKey(RegistrationDraft $draft): string
    {
        $publicId = (string) $draft->public_id;
        $signature = hash_hmac('sha256', $publicId, $this->signingKey());

        return $this->toUrlSafe(base64_encode($publicId.'|'.$signature));
    }

    public function decodeUrlKey(?string $urlKey): ?string
    {
        if (!$urlKey) {
            return null;
        }

        $decoded = base64_decode($this->fromUrlSafe($urlKey), true);
        if ($decoded === false || !str_contains($decoded, '|')) {
            return null;
        }

        [$publicId, $signature] = explode('|', $decoded, 2);
        if ($publicId === '' || $signature === '') {
            return null;
        }

        $expected = hash_hmac('sha256', $publicId, $this->signingKey());
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        return $publicId;
    }

    public function assertAccessible(RegistrationDraft $draft, Event $event): void
    {
        if ((int) $draft->event_id !== (int) $event->id) {
            abort(404);
        }

        if ($draft->isExpired()) {
            throw new InvalidArgumentException('Your registration progress has expired. Please start again.');
        }

        if ($draft->isCompleted()) {
            throw new InvalidArgumentException('This registration has already been completed.');
        }
    }

    public function assertCanAccessStep(RegistrationDraft $draft, RegistrationWizardStep $step): void
    {
        if (!$step->canAccessFrom($draft->current_step)) {
            throw new InvalidArgumentException('Please complete the previous steps first.');
        }

        $event = Event::query()->find($draft->event_id);
        if ($event?->email_verification_required && !$draft->isEmailVerified() && $step !== RegistrationWizardStep::Email) {
            throw new InvalidArgumentException('Please verify your email before continuing.');
        }
    }

    public function advanceTo(RegistrationDraft $draft, RegistrationWizardStep $step): RegistrationDraft
    {
        if ($step->number() > $draft->current_step->number()) {
            $draft->current_step = $step;
        }

        $draft->expires_at = now()->addHours(self::DRAFT_TTL_HOURS);
        $draft->save();

        return $draft->fresh();
    }

    public function savePayload(RegistrationDraft $draft, array $data, ?RegistrationWizardStep $advanceTo = null): RegistrationDraft
    {
        $draft->mergePayload($data);
        $draft->expires_at = now()->addHours(self::DRAFT_TTL_HOURS);

        if ($advanceTo && $advanceTo->number() > $draft->current_step->number()) {
            $draft->current_step = $advanceTo;
        }

        $draft->save();

        return $draft->fresh();
    }

    public function queueResumeCookie(string $plainToken): void
    {
        Cookie::queue(cookie(
            self::COOKIE_NAME,
            $plainToken,
            self::DRAFT_TTL_HOURS * 60,
            '/',
            null,
            request()->isSecure(),
            true,
            false,
            'Lax'
        ));
    }

    public function clearResumeCookie(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    public function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    public function generatePlainToken(): string
    {
        return Str::random(64);
    }

    public function rotateToken(RegistrationDraft $draft): string
    {
        $plainToken = $this->generatePlainToken();
        $draft->forceFill([
            'resume_token_hash' => $this->hashToken($plainToken),
            'expires_at' => now()->addHours(self::DRAFT_TTL_HOURS),
        ])->save();

        return $plainToken;
    }

    public function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    private function signingKey(): string
    {
        return (string) config('app.key');
    }

    private function toUrlSafe(string $value): string
    {
        return rtrim(strtr($value, '+/', '-_'), '=');
    }

    private function fromUrlSafe(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return strtr($value, '-_', '+/');
    }
}
