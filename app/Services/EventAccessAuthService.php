<?php

namespace App\Services;

use App\Models\OrganizationAdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EventAccessAuthService
{
    public function __construct(
        private EventContextService $eventContext
    ) {}

    public function authenticate(string $email, string $password): ?OrganizationAdminUser
    {
        [$eventId, $organizationId] = $this->eventContext->requiredContext();

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

    /**
     * @return array{user:OrganizationAdminUser,event_id:int,organization_id:int}|null
     */
    public function authenticateViaSsoToken(string $token): ?array
    {
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
        $eventId = (int) ($userData['event_id'] ?? 0);
        $organizationId = (int) ($userData['organization_id'] ?? 0);

        if (!$eventId || !$organizationId) {
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

        return [
            'user' => $user,
            'event_id' => $eventId,
            'organization_id' => $organizationId,
        ];
    }

    public function userIsAssignedToEvent(int $userId, int $eventId): bool
    {
        return DB::table('event_user')
            ->where('event_id', $eventId)
            ->where('organization_user_id', $userId)
            ->exists();
    }
}
