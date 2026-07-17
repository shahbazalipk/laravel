<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Http\Request;
use RuntimeException;

class EventContextService
{
    public function bootFromSession(): void
    {
        if (session()->has('event_id') && session()->has('org_id')) {
            $this->apply((int) session('event_id'), (int) session('org_id'));
        }
    }

    /**
     * @return array{event_id:int,organization_id:int,subdomain?:string}|null
     */
    public function resolveFromRequest(Request $request): ?array
    {
        if ($request->filled('ctx')) {
            return $this->decodeContext($request->string('ctx')->toString());
        }

        if ($subdomain = $this->subdomainFromHost($request)) {
            return $this->resolveFromSubdomain($subdomain);
        }

        return null;
    }

    public function apply(int $eventId, int $organizationId): void
    {
        config([
            'event.event_id' => $eventId,
            'event.org_id' => $organizationId,
        ]);

        session([
            'event_id' => $eventId,
            'org_id' => $organizationId,
        ]);
    }

    /**
     * @return array{0:int,1:int}
     */
    public function requiredContext(): array
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        if (!$eventId || !$organizationId) {
            throw new RuntimeException('Event context is missing. Open this event from the organization portal.');
        }

        return [(int) $eventId, (int) $organizationId];
    }

    /**
     * @return array{event_id:int,organization_id:int,subdomain:string}|null
     */
    public function decodeContext(string $context): ?array
    {
        [$payload, $signature] = array_pad(explode('.', $context, 2), 2, null);

        if (!$payload || !$signature || !hash_equals($this->sign($payload), $signature)) {
            return null;
        }

        $data = json_decode(base64_decode($payload, true) ?: '', true);

        if (!is_array($data) || ($data['exp'] ?? 0) < now()->timestamp) {
            return null;
        }

        if (!isset($data['event_id'], $data['organization_id'])) {
            return null;
        }

        return [
            'event_id' => (int) $data['event_id'],
            'organization_id' => (int) $data['organization_id'],
            'subdomain' => (string) ($data['subdomain'] ?? ''),
        ];
    }

    /**
     * @return array{event_id:int,organization_id:int,subdomain:string}|null
     */
    public function resolveFromSubdomain(string $subdomain): ?array
    {
        $event = Event::query()
            ->where('subdomain', $subdomain)
            ->first(['id', 'organization_id', 'subdomain']);

        if (!$event) {
            return null;
        }

        return [
            'event_id' => (int) $event->id,
            'organization_id' => (int) $event->organization_id,
            'subdomain' => (string) $event->subdomain,
        ];
    }

    private function subdomainFromHost(Request $request): ?string
    {
        $host = $request->getHost();
        $domain = config('event.event_domain', 'glimzo.ai');

        if (!str_ends_with($host, '.' . $domain)) {
            return null;
        }

        $subdomain = str_replace('.' . $domain, '', $host);

        return $subdomain !== $domain && $subdomain !== '' ? $subdomain : null;
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secret());
    }

    private function secret(): string
    {
        $secret = config('event.sso_secret');

        if (!$secret) {
            throw new RuntimeException('EVENT_SSO_SECRET must be configured.');
        }

        return $secret;
    }
}
