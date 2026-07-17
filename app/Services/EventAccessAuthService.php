<?php

namespace App\Services;

use App\Models\OrganizationAdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EventAccessAuthService
{
    public function authenticate(string $email, string $password): ?OrganizationAdminUser
    {
        [$eventId, $organizationId] = $this->requiredEventContext();

        $user = OrganizationAdminUser::query()
            ->where('email', $email)
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (!$this->userIsAssignedToEvent($user->id, $eventId)) {
            return null;
        }

        return $user;
    }

    public function authenticateViaSsoToken(string $token): ?OrganizationAdminUser
    {
        [$eventId, $organizationId] = $this->requiredEventContext();

        $portalUrl = rtrim((string) config('event.org_portal_url'), '/');

        if ($portalUrl === '') {
            throw new RuntimeException('ORG_PORTAL_URL must be configured for SSO login.');
        }

        $response = Http::timeout(10)->post("{$portalUrl}/api/validate-sso-token", [
            'token' => $token,
        ]);

        if (!$response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (!($payload['success'] ?? false)) {
            return null;
        }

        $userData = $payload['user'] ?? [];

        if ((int) ($userData['event_id'] ?? 0) !== (int) $eventId) {
            return null;
        }

        if ((int) ($userData['organization_id'] ?? 0) !== (int) $organizationId) {
            return null;
        }

        $user = OrganizationAdminUser::query()
            ->where('id', $userData['user_id'] ?? null)
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->first();

        if (!$user || !$this->userIsAssignedToEvent($user->id, $eventId)) {
            return null;
        }

        return $user;
    }

    public function userIsAssignedToEvent(int $userId, ?int $eventId = null): bool
    {
        [$resolvedEventId] = $this->requiredEventContext();

        return DB::table('event_user')
            ->where('event_id', $eventId ?? $resolvedEventId)
            ->where('organization_user_id', $userId)
            ->exists();
    }

    /**
     * @return array{0:int,1:int}
     */
    private function requiredEventContext(): array
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        if (!$eventId || !$organizationId) {
            throw new RuntimeException('EVENT_ID and ORG_ID must be configured in .env');
        }

        return [(int) $eventId, (int) $organizationId];
    }
}
